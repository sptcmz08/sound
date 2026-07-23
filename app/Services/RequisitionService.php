<?php

namespace App\Services;

use App\Enums\ProductType;
use App\Enums\RequisitionStatus;
use App\Enums\RequisitionType;
use App\Enums\StockDocumentType;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\User;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RequisitionService
{
    public function __construct(private StockService $stock, private AuditLogService $audit) {}

    public function create(array $data, User $user): Requisition
    {
        return DB::transaction(function () use ($data, $user) {
            $type = RequisitionType::from($data['request_type']);
            $request = Requisition::create([
                'request_no' => (string) Str::uuid(),
                'request_type' => $type,
                'status' => RequisitionStatus::PENDING,
                'warehouse_id' => $data['warehouse_id'],
                'target_product_id' => filled($data['target_product_id'] ?? null) ? $data['target_product_id'] : null,
                'target_quantity' => filled($data['target_quantity'] ?? null) ? $data['target_quantity'] : null,
                'department_name' => filled($data['department_name'] ?? null) ? $data['department_name'] : null,
                'purpose' => $data['purpose'],
                'note' => filled($data['note'] ?? null) ? $data['note'] : null,
                'requested_by' => $user->id,
                'requested_at' => now(),
            ]);
            $request->update(['request_no' => sprintf('REQ-%s-%06d', now()->format('Ym'), $request->id)]);

            if ($type->isBuild()) {
                $target = Product::with('components')->findOrFail($data['target_product_id']);
                $expectedType = $type === RequisitionType::BUILD_WIP ? ProductType::WIP : ProductType::FG;
                if ($target->product_type !== $expectedType || $target->components->isEmpty()) {
                    throw ValidationException::withMessages(['target_product_id' => 'สินค้าที่เลือกไม่มีสูตรส่วนประกอบ หรือประเภทไม่ตรงกับรายการสร้าง']);
                }
                $allowedComponentTypes = $type === RequisitionType::BUILD_WIP
                    ? [ProductType::PART]
                    : [ProductType::PART, ProductType::WIP];
                foreach ($target->components as $component) {
                    if (! in_array($component->product_type, $allowedComponentTypes, true)) {
                        throw ValidationException::withMessages(['target_product_id' => 'สูตรการผลิตมี SUPPLY หรือประเภทที่ไม่ถูกต้อง กรุณาแก้สูตรให้ใช้เฉพาะ PART สำหรับ WIP และ PART/WIP สำหรับ FG']);
                    }
                    $total = BigDecimal::of($component->pivot->quantity)
                        ->multipliedBy($data['target_quantity'])
                        ->toScale(4, RoundingMode::HALF_UP);
                    $request->items()->create(['product_id' => $component->id, 'quantity' => (string) $total, 'note' => 'ส่วนประกอบตามสูตร']);
                }
            } else {
                $expectedTypes = match ($type) {
                    RequisitionType::GENERAL_ISSUE => [ProductType::PART, ProductType::SUPPLY, ProductType::WIP, ProductType::FG],
                    RequisitionType::ISSUE_PART => [ProductType::PART],
                    RequisitionType::ISSUE_SUPPLY => [ProductType::SUPPLY],
                    RequisitionType::ISSUE_WIP => [ProductType::WIP],
                    RequisitionType::ISSUE_FG => [ProductType::FG],
                    default => throw new \LogicException('Unsupported requisition type'),
                };
                foreach ($data['items'] as $line) {
                    $product = Product::findOrFail($line['product_id']);
                    if (! in_array($product->product_type, $expectedTypes, true) || ! $product->is_active) {
                        throw ValidationException::withMessages(['items' => 'รายการสินค้ามีประเภทไม่ตรงกับประเภทการเบิก']);
                    }
                    $request->items()->create(['product_id' => $product->id, 'quantity' => $line['quantity'], 'note' => $line['note'] ?? null]);
                }
            }
            $this->audit->record($user, 'CREATE', 'requisition', $request->id, null, ['request_no' => $request->request_no, 'type' => $type->value]);

            return $request->fresh(['items.product.unit', 'targetProduct', 'warehouse', 'requester']);
        });
    }

    public function approve(Requisition $requisition, User $admin, ?string $signature = null): Requisition
    {
        return DB::transaction(function () use ($requisition, $admin, $signature) {
            $request = Requisition::whereKey($requisition->id)->lockForUpdate()->with(['items.product', 'targetProduct'])->firstOrFail();
            if ($request->status !== RequisitionStatus::PENDING) {
                throw ValidationException::withMessages(['request' => 'คำขอนี้ถูกดำเนินการแล้ว']);
            }

            $documents = [];
            foreach ($request->items->groupBy(fn ($item) => $item->product->product_type->value) as $productType => $items) {
                $documents[] = $this->stock->createAndPost($this->documentData($request, $items->map(fn ($item) => ['product_id' => $item->product_id, 'quantity' => $item->quantity])->all()), $this->outType(ProductType::from($productType)), $admin);
            }
            if ($request->request_type->isBuild()) {
                $calculatedCost = $request->targetProduct->components->sum(
                    fn ($component) => (float) $component->standard_cost * (float) $component->pivot->quantity
                );
                $request->targetProduct->update(['standard_cost' => $calculatedCost, 'updated_by' => $admin->id]);
                $documents[] = $this->stock->createAndPost($this->documentData($request, [['product_id' => $request->target_product_id, 'quantity' => $request->target_quantity]]), $this->inType($request->targetProduct->product_type), $admin);
            }
            $request->stockDocuments()->sync(collect($documents)->pluck('id'));
            $request->update(['status' => RequisitionStatus::APPROVED, 'approved_by' => $admin->id, 'approved_at' => now(), 'approval_signature' => $signature]);
            $this->audit->record($admin, 'APPROVE', 'requisition', $request->id, ['status' => 'PENDING'], ['status' => 'APPROVED', 'documents' => collect($documents)->pluck('document_no')->all()]);

            return $request->fresh(['items.product.unit', 'targetProduct.unit', 'warehouse', 'requester', 'approver', 'stockDocuments']);
        }, 3);
    }

    public function reject(Requisition $requisition, User $admin, string $reason): Requisition
    {
        return DB::transaction(function () use ($requisition, $admin, $reason) {
            $request = Requisition::whereKey($requisition->id)->lockForUpdate()->firstOrFail();
            if ($request->status !== RequisitionStatus::PENDING) {
                throw ValidationException::withMessages(['request' => 'คำขอนี้ถูกดำเนินการแล้ว']);
            }
            $request->update(['status' => RequisitionStatus::REJECTED, 'rejected_by' => $admin->id, 'rejected_at' => now(), 'rejection_reason' => $reason]);
            $this->audit->record($admin, 'REJECT', 'requisition', $request->id, ['status' => 'PENDING'], ['status' => 'REJECTED', 'reason' => $reason]);

            return $request->fresh();
        });
    }

    private function documentData(Requisition $request, array $items): array
    {
        return ['document_date' => today()->format('Y-m-d'), 'warehouse_id' => $request->warehouse_id, 'reference_no' => $request->request_no, 'department_name' => $request->department_name, 'purpose' => $request->purpose, 'note' => "อนุมัติตามคำขอ {$request->request_no}", 'idempotency_key' => (string) Str::uuid(), 'items' => $items];
    }

    private function inType(ProductType $type): StockDocumentType
    {
        return match ($type) {
            ProductType::PART => StockDocumentType::PART_IN, ProductType::SUPPLY => StockDocumentType::SUPPLY_IN, ProductType::WIP => StockDocumentType::WIP_IN, ProductType::FG => StockDocumentType::FG_IN
        };
    }

    private function outType(ProductType $type): StockDocumentType
    {
        return match ($type) {
            ProductType::PART => StockDocumentType::PART_OUT, ProductType::SUPPLY => StockDocumentType::SUPPLY_OUT, ProductType::WIP => StockDocumentType::WIP_OUT, ProductType::FG => StockDocumentType::FG_OUT
        };
    }
}
