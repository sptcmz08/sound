<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Enums\StockDocumentType;
use App\Models\Product;
use App\Services\StockService;
use App\Support\Quantity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockReceiptController extends Controller
{
    public function create()
    {
        return redirect()->route('operations.create', 'supplier-receive');
    }

    public function store(Request $request, StockService $stock)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'decimal:0,4', 'gt:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $product = Product::with('unit')->findOrFail($data['product_id']);
        $type = match ($product->product_type) {
            ProductType::PART => StockDocumentType::PART_IN,
            ProductType::SUPPLY => StockDocumentType::SUPPLY_IN,
            ProductType::WIP => StockDocumentType::WIP_IN,
            ProductType::FG => StockDocumentType::FG_IN,
        };
        $document = $stock->createAndPost([
            'document_date' => today()->format('Y-m-d'),
            'warehouse_id' => $data['warehouse_id'],
            'purpose' => 'รับ '.$product->product_type->value.' เข้าสต็อกจาก Supplier',
            'note' => $data['note'] ?? null,
            'idempotency_key' => (string) Str::uuid(),
            'items' => [['product_id' => $product->id, 'quantity' => $data['quantity']]],
        ], $type, $request->user());

        $typeName = $product->product_type->value;
        return redirect()->route('products.index', ['type' => $product->product_type->value])
            ->with('success', "รับ {$typeName} ({$product->name}) จำนวน ".Quantity::format($data['quantity'])." {$product->unit->name} เข้าสต็อกแล้ว ({$document->document_no})");
    }
}
