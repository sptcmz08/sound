<?php

namespace App\Http\Controllers;

use App\Enums\StockDocumentType;
use App\Http\Requests\StockDocumentRequest;
use App\Models\Product;
use App\Models\StockDocument;
use App\Models\Warehouse;
use App\Services\StockService;
use App\Support\Quantity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockDocumentController extends Controller
{
    private function type(string $type): StockDocumentType
    {
        return StockDocumentType::from(strtoupper($type));
    }

    public function index(Request $r, string $type)
    {
        $enum = $this->type($type);
        $documents = StockDocument::with('creator')->where('document_type', $enum)->latest()->paginate(20);

        return view('documents.index', compact('documents', 'enum'));
    }

    public function create(Request $r, string $type)
    {
        $enum = $this->type($type);
        if (in_array($enum, [StockDocumentType::ADJUST_IN, StockDocumentType::ADJUST_OUT], true)) {
            abort_unless($r->user()->isAdmin(), 403);
        }
        $products = Product::with(['unit', 'balances'])->where('is_active', 1)->when($enum->productType(), fn ($q, $v) => $q->where('product_type', $v))->orderBy('code')->get();
        $productOptions = $products->map(fn (Product $product) => [
            'id' => $product->id,
            'code' => $product->code,
            'barcode' => $product->barcode,
            'name' => $product->name,
            'image' => $product->image_path ? route('products.image', $product) : null,
            'unit' => $product->unit->name,
            'balances' => $product->balances->mapWithKeys(fn ($balance) => [
                $balance->warehouse_id => Quantity::format($balance->quantity),
            ]),
        ])->values();

        return view('documents.create', compact('enum', 'products', 'productOptions') + ['warehouses' => Warehouse::where('is_active', 1)->get(), 'idempotencyKey' => (string) Str::uuid()]);
    }

    public function store(StockDocumentRequest $r, string $type, StockService $service)
    {
        $enum = $this->type($type);
        if (in_array($enum, [StockDocumentType::ADJUST_IN, StockDocumentType::ADJUST_OUT], true)) {
            abort_unless($r->user()->isAdmin(), 403);
        }
        $doc = $service->createAndPost($r->validated(), $enum, $r->user());

        return redirect()->route('documents.show', $doc)->with('success', "บันทึก {$doc->document_no} แล้ว");
    }

    public function show(StockDocument $document)
    {
        return view('documents.show', ['document' => $document->load(['items.product.unit', 'items.options.optionItem.group', 'items.options.optionItem.optionProduct', 'warehouse', 'creator', 'poster', 'reversal'])]);
    }

    public function cancel(Request $r, StockDocument $document, StockService $service)
    {
        abort_unless($r->user()->isAdmin(), 403);
        $data = $r->validate(['reason' => ['required', 'string', 'max:1000']]);
        $reversal = $service->cancel($document, $r->user(), $data['reason']);

        return redirect()->route('documents.show', $reversal)->with('success', 'ยกเลิกและสร้างเอกสารย้อนรายการแล้ว');
    }
}
