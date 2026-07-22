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
    public function create(Request $request, string $operation)
    {
        $config = $this->config($operation);
        $filterType = $request->query('type');
        if ($filterType && in_array($filterType, ['PART', 'SUPPLY', 'WIP', 'FG'], true)) {
            $config['filter_type'] = $filterType;
            if ($filterType === 'PART') {
                $config['title'] = 'รับอะไหล่ผลิตเข้าสต็อก (PART)';
                $config['subtitle'] = 'รับอะไหล่และชิ้นส่วนสำหรับใช้ใน BOM เพื่อประกอบสินค้า';
            } elseif ($filterType === 'SUPPLY') {
                $config['title'] = 'รับวัสดุสิ้นเปลืองเข้าสต็อก (SUPPLY)';
                $config['subtitle'] = 'รับวัสดุสิ้นเปลือง (เช่น กาว, น้ำยา, เทป) ที่ไม่ต้องระบุใน BOM';
            } elseif ($filterType === 'WIP') {
                $config['title'] = 'รับ WIP เข้าสต็อกจาก Supplier';
                $config['subtitle'] = 'รับงานระหว่างประกอบที่จัดซื้อหรือรับจากผู้ผลิตภายนอก';
            } elseif ($filterType === 'FG') {
                $config['title'] = 'รับ FG เข้าสต็อกจาก Supplier';
                $config['subtitle'] = 'รับสินค้าสำเร็จรูปที่จัดซื้อหรือรับจากผู้ผลิตภายนอก';
            }
        }
        $products = Product::with(['unit', 'balances', 'optionGroups.items.optionProduct.balances', 'optionGroups.items.optionProduct.unit'])->where('is_active', true)
            ->when($config['product_types'], function ($query, $types) use ($filterType) {
                if ($filterType && in_array($filterType, $types, true)) {
                    return $query->where('product_type', $filterType);
                }
                return $query->whereIn('product_type', $types);
            })
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
                'optionGroups' => $product->optionGroups->map(fn ($group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'is_required' => $group->is_required,
                    'items' => $group->items->map(fn ($item) => [
                        'id' => $item->id,
                        'product_id' => $item->option_product_id,
                        'code' => $item->optionProduct->code,
                        'name' => $item->optionProduct->name,
                        'unit' => $item->optionProduct->unit->name,
                        'quantity' => Quantity::format($item->quantity),
                        'additional_price' => Quantity::format($item->additional_price),
                        'is_default' => $item->is_default,
                        'balances' => $item->optionProduct->balances->mapWithKeys(fn ($balance) => [
                            $balance->warehouse_id => Quantity::format($balance->quantity),
                        ]),
                    ]),
                ]),
            ])->values(),
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request, string $operation, StockService $stock)
    {
        $config = $this->config($operation);

        $rawItems = $request->input('items', []);
        if (is_array($rawItems)) {
            foreach ($rawItems as &$line) {
                if (isset($line['options']) && is_array($line['options'])) {
                    $line['options'] = array_values(array_filter($line['options'], function ($opt) {
                        return ! empty($opt['product_option_item_id']);
                    }));
                }
            }
            unset($line);
            $request->merge(['items' => $rawItems]);
        }

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
            'items.*.options' => ['nullable', 'array'],
            'items.*.options.*.product_option_item_id' => ['required', 'integer', 'distinct', 'exists:product_option_items,id'],
        ];
        $data = $request->validate($rules);
        $products = Product::with(['optionGroups.items'])->whereIn('id', collect($data['items'])->pluck('product_id'))->get()->keyBy('id');
        foreach ($data['items'] as $line) {
            $product = $products->get((int) $line['product_id']);
            if (! $product || ! $product->is_active || ($config['product_types'] && ! in_array($product->product_type->value, $config['product_types'], true))) {
                throw ValidationException::withMessages(['items' => 'พบรายการที่ไม่ตรงกับประเภทของงานนี้']);
            }

            $selectedOptionItemIds = collect($line['options'] ?? [])->pluck('product_option_item_id')->map(fn ($id) => (int) $id);
            if ($operation !== 'sale' && $selectedOptionItemIds->isNotEmpty()) {
                throw ValidationException::withMessages(['items' => 'เลือก Option ได้เฉพาะหน้าขายสินค้า FG เท่านั้น']);
            }
            if ($operation === 'sale') {
                $availableOptionItems = $product->optionGroups->flatMap(fn ($group) => $group->items)->keyBy('id');
                if ($selectedOptionItemIds->contains(fn ($id) => ! $availableOptionItems->has($id))) {
                    throw ValidationException::withMessages(['items' => "พบ Option ที่ไม่ได้อยู่ในสินค้า FG {$product->name}"]);
                }
                foreach ($product->optionGroups as $group) {
                    $selectedInGroup = $selectedOptionItemIds->intersect($group->items->pluck('id'));
                    if ($selectedInGroup->count() > 1) {
                        throw ValidationException::withMessages(['items' => "เลือก Option ในกลุ่ม '{$group->name}' ได้เพียง 1 รายการ"]);
                    }
                    if ($group->is_required && $selectedInGroup->isEmpty()) {
                        throw ValidationException::withMessages(['items' => "กรุณาเลือกตัวเลือกในกลุ่ม '{$group->name}' สำหรับสินค้า {$product->name}"]);
                    }
                }
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
                'title' => 'รับเข้าจาก Supplier', 'subtitle' => 'รับเข้าได้ทุกประเภท: PART, SUPPLY, WIP และ FG พร้อมบันทึกต้นทุนล่าสุด',
                'document_type' => StockDocumentType::SUPPLIER_IN, 'product_types' => [ProductType::PART->value, ProductType::SUPPLY->value, ProductType::WIP->value, ProductType::FG->value],
                'party_label' => 'Supplier', 'party_required' => true, 'note_required' => false, 'cost_input' => true, 'price_input' => false, 'direction' => 'in',
            ],
            'sale' => [
                'title' => 'ขาย', 'subtitle' => 'ตัด FG พร้อม WIP/PART ของ Option ที่ลูกค้าเลือก และบันทึกต้นทุนจริงสำหรับรายงานกำไร',
                'document_type' => StockDocumentType::SALE_OUT, 'product_types' => [ProductType::FG->value],
                'party_label' => 'ลูกค้า', 'party_required' => true, 'note_required' => false, 'cost_input' => false, 'price_input' => true, 'direction' => 'out',
            ],
            'claim' => [
                'title' => 'รับเคลมจากลูกค้า', 'subtitle' => 'รับ PART, WIP หรือ FG ที่ลูกค้าส่งเคลมกลับเข้าสต็อกแยกจากยอดขาย',
                'document_type' => StockDocumentType::CLAIM_IN, 'product_types' => [ProductType::PART->value, ProductType::WIP->value, ProductType::FG->value],
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
