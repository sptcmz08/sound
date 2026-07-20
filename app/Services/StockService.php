<?php

namespace App\Services;

use App\Enums\StockDocumentStatus;
use App\Enums\StockDocumentType;
use App\Enums\StockTransactionType;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockDocument;
use App\Models\StockTransaction;
use App\Models\User;
use Brick\Math\BigDecimal;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function __construct(private DocumentNumberService $numbers, private AuditLogService $audit) {}

    public function createAndPost(array $data, StockDocumentType $type, User $user): StockDocument
    {
        if ($key = $data['idempotency_key'] ?? null) {
            if ($existing = StockDocument::where('idempotency_key', $key)->first()) {
                return $existing;
            }
        }
        try {
            return DB::transaction(function () use ($data, $type, $user) {
                if ($key = $data['idempotency_key'] ?? null) {
                    if ($existing = StockDocument::where('idempotency_key', $key)->lockForUpdate()->first()) {
                        return $existing;
                    }
                }
                $doc = StockDocument::create(['document_no' => $this->numbers->next($type), 'document_type' => $type, 'document_date' => $data['document_date'], 'warehouse_id' => $data['warehouse_id'], 'reference_no' => $data['reference_no'] ?? null, 'contact_name' => $data['contact_name'] ?? null, 'department_name' => $data['department_name'] ?? null, 'purpose' => $data['purpose'] ?? null, 'note' => $data['note'] ?? null, 'status' => StockDocumentStatus::DRAFT, 'idempotency_key' => $data['idempotency_key'] ?? null, 'created_by' => $user->id]);
                $seen = [];
                foreach ($data['items'] as $line) {
                    if (isset($seen[$line['product_id']])) {
                        throw ValidationException::withMessages(['items' => 'ห้ามมีสินค้าซ้ำในเอกสารเดียว']);
                    }$seen[$line['product_id']] = true;
                    $product = Product::withTrashed()->findOrFail($line['product_id']);
                    if (! $product->is_active || $product->trashed()) {
                        throw ValidationException::withMessages(['items' => 'สินค้าไม่พร้อมใช้งาน']);
                    }if ($type->productType() && $product->product_type !== $type->productType()) {
                        throw ValidationException::withMessages(['items' => 'ประเภทสินค้าไม่ตรงกับประเภทเอกสาร']);
                    }if (BigDecimal::of($line['quantity'])->isLessThanOrEqualTo(0)) {
                        throw ValidationException::withMessages(['items' => 'จำนวนต้องมากกว่า 0']);
                    }$item = $doc->items()->create([
                        'product_id' => $product->id,
                        'quantity' => (string) $line['quantity'],
                        'unit_cost' => (string) ($line['unit_cost'] ?? $product->standard_cost ?? 0),
                        'unit_price' => (string) ($line['unit_price'] ?? 0),
                        'unit_id' => $product->unit_id,
                        'note' => $line['note'] ?? null,
                    ]);
                    $this->apply($doc, $item->id, $product, $type->isInbound(), (string) $line['quantity'], $user, $this->transactionType($type));
                    
                    if (isset($line['options']) && is_array($line['options'])) {
                        foreach ($line['options'] as $optLine) {
                            $optionItem = \App\Models\ProductOptionItem::with('optionProduct')->findOrFail($optLine['product_option_item_id']);
                            
                            $optQtyPerUnit = BigDecimal::of($optionItem->quantity);
                            $mainQty = BigDecimal::of($line['quantity']);
                            $totalOptQty = $mainQty->multipliedBy($optQtyPerUnit);
                            
                            $item->options()->create([
                                'product_option_item_id' => $optionItem->id,
                                'quantity' => (string) $totalOptQty,
                            ]);
                            
                            $this->apply(
                                $doc,
                                $item->id,
                                $optionItem->optionProduct,
                                false,
                                (string) $totalOptQty,
                                $user,
                                StockTransactionType::OUT
                            );
                        }
                    }

                    if ($type === StockDocumentType::SUPPLIER_IN && array_key_exists('unit_cost', $line)) {
                        $product->update(['standard_cost' => (string) $line['unit_cost'], 'updated_by' => $user->id]);
                    }
                }
                $doc->update(['status' => StockDocumentStatus::POSTED, 'posted_by' => $user->id, 'posted_at' => now()]);
                $this->audit->record($user, 'POST', 'stock_document', $doc->id, null, ['document_no' => $doc->document_no, 'type' => $type->value]);

                return $doc->fresh(['items.product', 'warehouse']);
            }, 3);
        } catch (QueryException $e) {
            if (($data['idempotency_key'] ?? null) && ($existing = StockDocument::where('idempotency_key', $data['idempotency_key'])->first())) {
                return $existing;
            }throw $e;
        }
    }

    public function cancel(StockDocument $document, User $user, string $reason): StockDocument
    {
        return DB::transaction(function () use ($document, $user, $reason) {
            $original = StockDocument::whereKey($document->id)->lockForUpdate()->firstOrFail();
            if ($original->status !== StockDocumentStatus::POSTED) {
                throw ValidationException::withMessages(['document' => 'ยกเลิกได้เฉพาะเอกสารที่ POSTED และยังไม่ถูกยกเลิก']);
            }$reversal = StockDocument::create(['document_no' => $this->numbers->next(StockDocumentType::REVERSAL), 'document_type' => StockDocumentType::REVERSAL, 'document_date' => today(), 'warehouse_id' => $original->warehouse_id, 'reference_no' => $original->document_no, 'note' => $reason, 'status' => StockDocumentStatus::DRAFT, 'created_by' => $user->id, 'reversal_of_id' => $original->id]);
            foreach ($original->transactions()->orderBy('id')->get() as $txn) {
                $in = BigDecimal::of($txn->quantity_in)->isGreaterThan(0);
                $product = Product::withTrashed()->findOrFail($txn->product_id);
                $this->apply($reversal, null, $product, ! $in, $in ? (string) $txn->quantity_in : (string) $txn->quantity_out, $user, $in ? StockTransactionType::REVERSAL_OUT : StockTransactionType::REVERSAL_IN);
            }$reversal->update(['status' => StockDocumentStatus::POSTED, 'posted_by' => $user->id, 'posted_at' => now()]);
            $original->update(['status' => StockDocumentStatus::CANCELLED, 'cancelled_by' => $user->id, 'cancelled_at' => now()]);
            $this->audit->record($user, 'CANCEL', 'stock_document', $original->id, ['status' => 'POSTED'], ['status' => 'CANCELLED', 'reversal' => $reversal->document_no, 'reason' => $reason]);

            return $reversal->fresh();
        }, 3);
    }

    private function apply(StockDocument $doc, ?int $itemId, Product $product, bool $in, string $quantity, User $user, StockTransactionType $transactionType): void
    {
        StockBalance::query()->insertOrIgnore(['product_id' => $product->id, 'warehouse_id' => $doc->warehouse_id, 'quantity' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $balance = StockBalance::where(['product_id' => $product->id, 'warehouse_id' => $doc->warehouse_id])->lockForUpdate()->firstOrFail();
        $before = BigDecimal::of($balance->quantity);
        $qty = BigDecimal::of($quantity);
        $after = $in ? $before->plus($qty) : $before->minus($qty);
        if ($after->isNegative()) {
            throw ValidationException::withMessages(['items' => "สินค้า {$product->code} มียอดคงเหลือไม่เพียงพอ"]);
        }$value = (string) $after->toScale(4);
        $balance->update(['quantity' => $value]);
        StockTransaction::create(['transaction_uuid' => (string) Str::uuid(), 'stock_document_id' => $doc->id, 'stock_document_item_id' => $itemId, 'product_id' => $product->id, 'warehouse_id' => $doc->warehouse_id, 'transaction_type' => $transactionType, 'quantity_in' => $in ? $quantity : 0, 'quantity_out' => $in ? 0 : $quantity, 'balance_after' => $value, 'occurred_at' => now(), 'created_by' => $user->id, 'note' => $doc->note, 'created_at' => now()]);
    }

    private function transactionType(StockDocumentType $type): StockTransactionType
    {
        return match ($type) {
            StockDocumentType::PART_IN,StockDocumentType::WIP_IN,StockDocumentType::FG_IN,StockDocumentType::SUPPLIER_IN,StockDocumentType::CLAIM_IN => StockTransactionType::IN,
            StockDocumentType::PART_OUT,StockDocumentType::WIP_OUT,StockDocumentType::FG_OUT,StockDocumentType::SALE_OUT,StockDocumentType::WASTE_OUT => StockTransactionType::OUT,
            StockDocumentType::ADJUST_IN => StockTransactionType::ADJUST_IN,
            StockDocumentType::ADJUST_OUT => StockTransactionType::ADJUST_OUT,
            default => throw new \LogicException('Unsupported document type')
        };
    }
}
