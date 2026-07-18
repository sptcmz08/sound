<?php

namespace App\Services;

use App\Models\StockBalance;
use App\Models\StockTransaction;

class StockReportService
{
    public function balances(array $filters = [])
    {
        return StockBalance::with(['product.unit', 'warehouse'])->when($filters['q'] ?? null, fn ($q, $v) => $q->whereHas('product', fn ($p) => $p->where(fn ($x) => $x->where('code', 'like', "%$v%")->orWhere('name', 'like', "%$v%")->orWhere('barcode', 'like', "%$v%"))))->when($filters['type'] ?? null, fn ($q, $v) => $q->whereHas('product', fn ($p) => $p->where('product_type', $v)))->orderBy('product_id');
    }

    public function card(int $productId)
    {
        return StockTransaction::with(['document', 'user'])->where('product_id', $productId)->orderBy('occurred_at')->orderBy('id');
    }
}
