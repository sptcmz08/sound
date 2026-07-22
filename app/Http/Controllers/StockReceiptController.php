<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Enums\StockDocumentType;
use App\Models\Product;
use App\Services\StockService;
use App\Support\Quantity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockReceiptController extends Controller
{
    public function create()
    {
        return redirect()->route('products.index', ['receive' => 1]);
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
        if (! in_array($product->product_type, [ProductType::PART, ProductType::SUPPLY], true)) {
            throw ValidationException::withMessages(['product_id' => 'การรับเข้าจาก Supplier รองรับเฉพาะ PART หรือ SUPPLY เท่านั้น; WIP และ FG ต้องเข้าสต็อกจากขั้นตอนผลิต']);
        }
        $type = match ($product->product_type) {
            ProductType::PART => StockDocumentType::PART_IN,
            ProductType::SUPPLY => StockDocumentType::SUPPLY_IN,
            ProductType::WIP => StockDocumentType::WIP_IN,
            ProductType::FG => StockDocumentType::FG_IN,
        };
        $document = $stock->createAndPost([
            'document_date' => today()->format('Y-m-d'),
            'warehouse_id' => $data['warehouse_id'],
            'purpose' => $product->product_type === ProductType::SUPPLY ? 'รับวัสดุสิ้นเปลืองเข้าสต็อก (SUPPLY)' : 'รับอะไหล่ผลิตเข้าสต็อก (PART)',
            'note' => $data['note'] ?? null,
            'idempotency_key' => (string) Str::uuid(),
            'items' => [['product_id' => $product->id, 'quantity' => $data['quantity']]],
        ], $type, $request->user());

        $typeName = $product->product_type === ProductType::SUPPLY ? 'วัสดุสิ้นเปลือง' : 'อะไหล่ PART';
        return redirect()->route('products.index', ['type' => $product->product_type->value])
            ->with('success', "รับ {$typeName} ({$product->name}) จำนวน ".Quantity::format($data['quantity'])." {$product->unit->name} เข้าสต็อกแล้ว ({$document->document_no})");
    }
}
