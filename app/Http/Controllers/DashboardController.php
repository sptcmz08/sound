<?php

namespace App\Http\Controllers;

use App\Enums\RequisitionStatus;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\StockBalance;
use App\Models\StockDocumentItem;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $typeCounts = Product::where('is_active', true)
            ->selectRaw('product_type, COUNT(*) as total')
            ->groupBy('product_type')
            ->pluck('total', 'product_type');
        $stockValue = StockBalance::join('products', 'products.id', '=', 'stock_balances.product_id')
            ->sum(DB::raw('stock_balances.quantity * products.standard_cost'));
        $stockQuantity = StockBalance::sum('quantity');
        $lowStockQuery = StockBalance::with(['product.unit', 'warehouse'])
            ->join('products', 'products.id', '=', 'stock_balances.product_id')
            ->whereColumn('stock_balances.quantity', '<=', 'products.minimum_stock')
            ->where('products.is_active', true)
            ->select('stock_balances.*');
        $monthSales = StockDocumentItem::whereHas('document', fn ($query) => $query
            ->where('document_type', 'SALE_OUT')
            ->whereBetween('document_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]))
            ->sum(DB::raw('quantity * unit_price'));

        return view('dashboard', [
            'partCount' => (int) ($typeCounts['PART'] ?? 0),
            'supplyCount' => (int) ($typeCounts['SUPPLY'] ?? 0),
            'wipCount' => (int) ($typeCounts['WIP'] ?? 0),
            'fgCount' => (int) ($typeCounts['FG'] ?? 0),
            'productCount' => (int) $typeCounts->sum(),
            'pendingCount' => Requisition::where('status', RequisitionStatus::PENDING)->count(),
            'stockLines' => StockBalance::where('quantity', '>', 0)->count(),
            'stockValue' => (float) $stockValue,
            'stockQuantity' => (float) $stockQuantity,
            'monthSales' => (float) $monthSales,
            'lowStockCount' => (clone $lowStockQuery)->count(),
            'lowStocks' => $lowStockQuery->orderBy('stock_balances.quantity')->limit(6)->get(),
            'recentTransactions' => StockTransaction::with(['product.unit', 'document', 'user'])->latest('occurred_at')->limit(7)->get(),
            'recentRequests' => Requisition::with(['requester', 'targetProduct', 'items.product'])->latest()->limit(6)->get(),
        ]);
    }
}
