<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Enums\StockDocumentType;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;
use App\Support\Quantity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BusinessOperationController extends Controller
{
    public function create(string $operation)
    {
        $config = $this->config($operation);
        $products = Product::with(['unit', 'balances'])->where('is_active', true)
            ->when($config['product_types'], fn ($query, $types) => $query->whereIn('product_type', $types))
            ->orderBy('product_type')->orderBy('code')->get();

        return view('operations.create', [
            'operation' => $operation,
            'config' => $config,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('code')->get(),
            'productOptions' => $products->map(fn (Product $product) => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'type' => $product->product_type->value,
                'unit' => $product->unit->name,
                'cost' => Quantity::format($product->standard_cost),
                'price' => Quantity::format($product->sale_price),
                'balances' => $product->balances->mapWithKeys(fn ($balance) => [
                    $balance->warehouse_id => Quantity::format($balance->quantity),
                ]),
            ])->values(),
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request, string $operation, StockService $stock)
    {
        $config = $this->config($operation);
        $rules = [
            'document_date' => ['required', 'date'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'contact_name' => [$config['party_required'] ? 'required' : 'nullable', 'string', 'max:255'],
            'note' => [$config['note_required'] ? 'required' : 'nullable', 'string', 'max:2000'],
            'idempotency_key' => ['required', 'uuid'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'items.*.quantity' => ['required', 'decimal:0,4', 'gt:0'],
            'items.*.unit_cost' => [$config['cost_input'] ? 'required' : 'nullable', 'decimal:0,4', 'gte:0'],
            'items.*.unit_price' => [$config['price_input'] ? 'required' : 'nullable', 'decimal:0,4', 'gte:0'],
        ];
        $data = $request->validate($rules);
        $products = Product::whereIn('id', collect($data['items'])->pluck('product_id'))->get()->keyBy('id');
        foreach ($data['items'] as $line) {
            $product = $products->get((int) $line['product_id']);
            if (! $product || ! $product->is_active || ($config['product_types'] && ! in_array($product->product_type->value, $config['product_types'], true))) {
                throw ValidationException::withMessages(['items' => 'พบรายการที่ไม่ตรงกับประเภทของงานนี้']);
            }
        }

        $document = $stock->createAndPost([
            ...$data,
            'purpose' => $config['title'],
        ], $config['document_type'], $request->user());

        return redirect()->route('documents.show', $document)->with('success', "บันทึก {$config['title']} เรียบร้อยแล้ว ({$document->document_no})");
    }

    private function config(string $operation): array
    {
        return match ($operation) {
            'supplier-receive' => [
                'title' => 'รับเข้าจาก Supplier', 'subtitle' => 'รับ PART และวัสดุสิ้นเปลืองเข้าสต็อก พร้อมบันทึกต้นทุนล่าสุด',
                'document_type' => StockDocumentType::SUPPLIER_IN, 'product_types' => [ProductType::PART->value],
                'party_label' => 'Supplier', 'party_required' => true, 'note_required' => false, 'cost_input' => true, 'price_input' => false, 'direction' => 'in',
            ],
            'sale' => [
                'title' => 'ขาย', 'subtitle' => 'ตัด FG ออกจากสต็อกและบันทึกราคาขายสำหรับรายงานกำไร',
                'document_type' => StockDocumentType::SALE_OUT, 'product_types' => [ProductType::FG->value],
                'party_label' => 'ลูกค้า', 'party_required' => true, 'note_required' => false, 'cost_input' => false, 'price_input' => true, 'direction' => 'out',
            ],
            'claim' => [
                'title' => 'รับเคลมจากลูกค้า', 'subtitle' => 'รับ PART, WIP หรือ FG ที่ลูกค้าส่งเคลมกลับเข้าสต็อกแยกจากยอดขาย',
                'document_type' => StockDocumentType::CLAIM_IN, 'product_types' => null,
                'party_label' => 'ลูกค้า', 'party_required' => true, 'note_required' => true, 'cost_input' => false, 'price_input' => false, 'direction' => 'in',
            ],
            'waste' => [
                'title' => 'บันทึกของเสีย', 'subtitle' => 'ตัด PART, WIP หรือ FG ที่เสียจากลูกค้าหรือการผลิตออกจากสต็อก',
                'document_type' => StockDocumentType::WASTE_OUT, 'product_types' => null,
                'party_label' => 'แหล่งที่มา', 'party_required' => false, 'note_required' => true, 'cost_input' => false, 'price_input' => false, 'direction' => 'out',
            ],
            default => abort(404),
        };
    }
}
