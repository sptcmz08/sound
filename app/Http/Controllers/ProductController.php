<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('unit')->withCount('components')->withSum('balances', 'quantity')
            ->when($request->q, fn ($query, $value) => $query->where(
                fn ($inner) => $inner->where('code', 'like', "%{$value}%")->orWhere('name', 'like', "%{$value}%")
            ))
            ->when($request->type, fn ($query, $value) => $query->where('product_type', $value))
            ->latest()->paginate(20)->withQueryString();

        return view('products.index', [
            'products' => $products,
            'receiptProducts' => Product::with('unit')->where('is_active', true)->orderBy('code')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function create()
    {
        $units = Unit::where('is_active', true)->orderBy('code')->get();
        if ($units->isEmpty()) {
            return redirect()->route('settings')->withErrors(['unit' => 'กรุณาเพิ่มหน่วยนับก่อนเพิ่มสินค้า']);
        }

        return view('products.form', $this->formData(new Product, $units));
    }

    public function store(ProductRequest $request, AuditLogService $audit)
    {
        $product = DB::transaction(function () use ($request, $audit) {
            $data = $request->safe()->except(['image', 'components']);
            $data['created_by'] = $data['updated_by'] = $request->user()->id;
            $data['is_active'] = $request->boolean('is_active');
            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('products', 'public');
            }
            $this->validateComponents($data['product_type'], $request->input('components', []));
            $product = Product::create($data);
            $this->syncComponents($product, $request->input('components', []));
            $audit->record($request->user(), 'CREATE', 'product', $product->id, null, $product->load('components')->toArray());

            return $product;
        });

        return redirect()->route('products.index')->with('success', "เพิ่ม {$product->name} และสูตรส่วนประกอบแล้ว");
    }

    public function edit(Product $product)
    {
        $units = Unit::where(fn ($query) => $query->where('is_active', true)->orWhere('id', $product->unit_id))
            ->orderBy('code')->get();

        return view('products.form', $this->formData($product->load('components'), $units));
    }

    public function update(ProductRequest $request, Product $product, AuditLogService $audit)
    {
        DB::transaction(function () use ($request, $product, $audit) {
            $old = $product->load('components')->toArray();
            $data = $request->safe()->except(['image', 'components']);
            $data['updated_by'] = $request->user()->id;
            $data['is_active'] = $request->boolean('is_active');
            if ($request->hasFile('image')) {
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $data['image_path'] = $request->file('image')->store('products', 'public');
            }
            $this->validateComponents($data['product_type'], $request->input('components', []), $product->id);
            $product->update($data);
            $this->syncComponents($product, $request->input('components', []));
            $audit->record($request->user(), 'UPDATE', 'product', $product->id, $old, $product->fresh()->load('components')->toArray());
        });

        return redirect()->route('products.index')->with('success', 'บันทึกสินค้าและสูตรแล้ว');
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
        if ($type === ProductType::PART && count($components)) {
            throw ValidationException::withMessages(['components' => 'อะไหล่ทั่วไปไม่ต้องมีสูตรส่วนประกอบ']);
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
                throw ValidationException::withMessages(['components' => 'ส่วนประกอบไม่ถูกต้อง: วิชใช้ได้เฉพาะอะไหล่ ส่วน FG ใช้อะไหล่หรือวิช']);
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
}
