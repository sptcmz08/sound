<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Unit;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function image(Product $product)
    {
        abort_unless($product->image_path && Storage::disk('public')->exists($product->image_path), 404);

        return Storage::disk('public')->response($product->image_path, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function index(Request $request)
    {
        $selectedType = $request->input('type', ProductType::PART->value);
        $products = Product::with('unit')->withCount(['components', 'optionGroups'])->withSum('balances', 'quantity')
            ->when($request->q, fn ($query, $value) => $query->where(
                fn ($inner) => $inner->where('code', 'like', "%{$value}%")->orWhere('name', 'like', "%{$value}%")
            ))
            ->where('product_type', $selectedType)
            ->latest()->paginate(20)->withQueryString();

        return view('products.index', [
            'products' => $products,
        ]);
    }

    public function create(Request $request)
    {
        $units = Unit::where('is_active', true)->orderBy('code')->get();
        if ($units->isEmpty()) {
            return redirect()->route('settings')->withErrors(['unit' => 'กรุณาเพิ่มหน่วยนับก่อนเพิ่มสินค้า']);
        }

        $product = new Product;
        $requestedType = $request->query('type');
        if (in_array($requestedType, array_column(ProductType::cases(), 'value'), true)) {
            $product->product_type = ProductType::from($requestedType);
        }

        return view('products.form', $this->formData($product, $units));
    }

    public function store(ProductRequest $request, AuditLogService $audit)
    {
        $product = DB::transaction(function () use ($request, $audit) {
            $data = $request->safe()->except(['image', 'components', 'option_groups']);
            $data['standard_cost'] = $data['standard_cost'] ?? 0;
            $data['sale_price'] = $data['sale_price'] ?? 0;
            $data['created_by'] = $data['updated_by'] = $request->user()->id;
            $data['is_active'] = $request->boolean('is_active');
            $data['is_consumable'] = $request->boolean('is_consumable');
            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('products', 'public');
            }
            $this->validateComponents($data['product_type'], $request->input('components', []));
            $this->validateOptionGroups($data['product_type'], $request->input('option_groups', []));
            $product = Product::create($data);
            $this->syncComponents($product, $request->input('components', []));
            $this->syncOptionGroups($product, $request->input('option_groups', []));
            $audit->record($request->user(), 'CREATE', 'product', $product->id, null, $product->load(['components', 'optionGroups.items.optionProduct'])->toArray());

            return $product;
        });

        if ($request->input('modal') == '1' || $request->query('modal') == '1') {
            $targetUrl = route('products.index', ['type' => $product->product_type->value]);
            return response("<script>if(window.parent && window.parent !== window){ window.parent.location.href='{$targetUrl}'; } else { window.location.href='{$targetUrl}'; }</script>");
        }

        return redirect()->route('products.index', ['type' => $product->product_type->value])->with('success', "เพิ่ม {$product->name} แล้ว");
    }

    public function edit(Product $product)
    {
        $units = Unit::where(fn ($query) => $query->where('is_active', true)->orWhere('id', $product->unit_id))
            ->orderBy('code')->get();

        return view('products.form', $this->formData($product->load(['components', 'optionGroups.items.optionProduct']), $units));
    }

    public function update(ProductRequest $request, Product $product, AuditLogService $audit)
    {
        DB::transaction(function () use ($request, $product, $audit) {
            $old = $product->load(['components', 'optionGroups.items.optionProduct'])->toArray();
            $data = $request->safe()->except(['image', 'components', 'option_groups']);
            $data['updated_by'] = $request->user()->id;
            $data['is_active'] = $request->boolean('is_active');
            $data['is_consumable'] = $request->boolean('is_consumable');
            if ($request->hasFile('image')) {
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $data['image_path'] = $request->file('image')->store('products', 'public');
            }
            $this->validateComponents($data['product_type'], $request->input('components', []), $product->id);
            $this->validateOptionGroups($data['product_type'], $request->input('option_groups', []));
            $product->update($data);
            $this->syncComponents($product, $request->input('components', []));
            $this->syncOptionGroups($product, $request->input('option_groups', []));
            $audit->record($request->user(), 'UPDATE', 'product', $product->id, $old, $product->fresh()->load(['components', 'optionGroups.items.optionProduct'])->toArray());
        });

        if ($request->input('modal') == '1' || $request->query('modal') == '1') {
            $targetUrl = route('products.index', ['type' => $product->fresh()->product_type->value]);
            return response("<script>if(window.parent && window.parent !== window){ window.parent.location.href='{$targetUrl}'; } else { window.location.href='{$targetUrl}'; }</script>");
        }

        return redirect()->route('products.index', ['type' => $product->fresh()->product_type->value])->with('success', 'บันทึกสินค้าและสูตรแล้ว');
    }

    public function quickImage(Request $request, Product $product)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $path = $request->file('image')->store('products', 'public');
        $product->update(['image_path' => $path]);

        return back()->with('success', 'อัปโหลดรูปสินค้า (' . $product->name . ') เรียบร้อยแล้ว');
    }

    public function destroy(Request $request, Product $product, AuditLogService $audit)
    {
        abort_unless($request->user()->isAdmin(), 403);
        if ($product->transactions()->exists() || $product->usedInProducts()->exists()) {
            $product->update(['is_active' => false, 'updated_by' => $request->user()->id]);
            $action = 'DEACTIVATE';
        } else {
            $product->delete();
            $action = 'DELETE';
        }
        $audit->record($request->user(), $action, 'product', $product->id);

        return back()->with('success', 'ดำเนินการแล้ว');
    }

    private function formData(Product $product, $units): array
    {
        return [
            'product' => $product,
            'units' => $units,
            'componentProducts' => Product::with('unit')->where('is_active', true)
                ->when($product->exists, fn ($query) => $query->whereKeyNot($product->id))
                ->whereIn('product_type', [ProductType::PART, ProductType::WIP])
                ->orderBy('product_type')->orderBy('code')->get(),
        ];
    }

    private function validateComponents(string $typeValue, array $components, ?int $productId = null): void
    {
        $type = ProductType::from($typeValue);
        if (in_array($type, [ProductType::PART, ProductType::SUPPLY], true) && count($components)) {
            throw ValidationException::withMessages(['components' => 'PART และ SUPPLY ไม่ต้องมีสูตรส่วนประกอบ']);
        }
        if (in_array($type, [ProductType::WIP, ProductType::FG], true) && ! count($components)) {
            throw ValidationException::withMessages(['components' => 'กรุณาเพิ่มส่วนประกอบอย่างน้อย 1 รายการ']);
        }

        $products = Product::whereIn('id', collect($components)->pluck('product_id'))->get()->keyBy('id');
        foreach ($components as $component) {
            $item = $products->get((int) $component['product_id']);
            $allowed = $type === ProductType::WIP
                ? $item?->product_type === ProductType::PART
                : in_array($item?->product_type, [ProductType::PART, ProductType::WIP], true);
            if (! $item || ! $item->is_active || ! $allowed || $item->id === $productId) {
                throw ValidationException::withMessages(['components' => 'ส่วนประกอบไม่ถูกต้อง: WIP ใช้ได้เฉพาะ PART ส่วน FG ใช้ PART หรือ WIP']);
            }
        }
    }

    private function syncComponents(Product $product, array $components): void
    {
        $sync = collect($components)->mapWithKeys(fn ($line) => [
            (int) $line['product_id'] => ['quantity' => $line['quantity']],
        ])->all();
        $product->components()->sync($sync);
    }

    private function syncOptionGroups(Product $product, array $optionGroups): void
    {
        if ($product->product_type !== ProductType::FG) {
            $product->optionGroups()->delete();
            return;
        }

        $existingGroupIds = [];
        foreach ($optionGroups as $groupIndex => $groupData) {
            $group = $product->optionGroups()->updateOrCreate(
                ['id' => $groupData['id'] ?? null],
                [
                    'name' => $groupData['name'],
                    'is_required' => filter_var($groupData['is_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'sort_order' => $groupIndex,
                ]
            );
            $existingGroupIds[] = $group->id;

            $existingItemIds = [];
            foreach ($groupData['items'] as $itemIndex => $itemData) {
                $item = $group->items()->updateOrCreate(
                    ['id' => $itemData['id'] ?? null],
                    [
                        'option_product_id' => $itemData['option_product_id'],
                        'quantity' => $itemData['quantity'],
                        'additional_price' => $itemData['additional_price'] ?? 0,
                        'is_default' => filter_var($itemData['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'sort_order' => $itemIndex,
                    ]
                );
                $existingItemIds[] = $item->id;
            }
            $group->items()->whereNotIn('id', $existingItemIds)->delete();
        }
        $product->optionGroups()->whereNotIn('id', $existingGroupIds)->delete();
    }

    private function validateOptionGroups(string $typeValue, array $optionGroups): void
    {
        $type = ProductType::from($typeValue);
        if ($type !== ProductType::FG && count($optionGroups)) {
            throw ValidationException::withMessages(['option_groups' => 'สินค้าประเภท FG เท่านั้นที่สามารถกำหนดตัวเลือกเสริม (Options) ได้']);
        }
        if ($type === ProductType::FG) {
            foreach ($optionGroups as $g) {
                if (blank($g['name'] ?? null)) {
                    throw ValidationException::withMessages(['option_groups' => 'กรุณาระบุชื่อกลุ่ม Option']);
                }
                $items = array_filter($g['items'] ?? [], fn ($i) => ! empty($i['option_product_id']));
                if (empty($items)) {
                    throw ValidationException::withMessages(['option_groups' => "กลุ่ม '{$g['name']}' ต้องมีรายการตัวเลือกอย่างน้อย 1 รายการ"]);
                }
                $optionProductIds = collect($items)->pluck('option_product_id');
                $optionProducts = Product::whereIn('id', $optionProductIds)->get()->keyBy('id');
                foreach ($items as $item) {
                    $optProduct = $optionProducts->get((int) $item['option_product_id']);
                    if (! $optProduct || ! $optProduct->is_active || ! in_array($optProduct->product_type, [ProductType::PART, ProductType::WIP], true)) {
                        throw ValidationException::withMessages(['option_groups' => "รายการ Option ในกลุ่ม '{$g['name']}' ต้องเป็นสินค้าประเภท PART หรือ WIP ที่เปิดใช้งานเท่านั้น"]);
                    }
                }
            }
        }
    }
}
