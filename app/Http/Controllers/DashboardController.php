<?php

namespace App\Http\Controllers;

use App\Enums\RequisitionStatus;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\StockBalance;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboard', [
            'partCount' => Product::where('product_type', 'PART')->where('is_active', true)->count(),
            'wipCount' => Product::where('product_type', 'WIP')->where('is_active', true)->count(),
            'fgCount' => Product::where('product_type', 'FG')->where('is_active', true)->count(),
            'pendingCount' => Requisition::where('status', RequisitionStatus::PENDING)->count(),
            'stockLines' => StockBalance::where('quantity', '>', 0)->count(),
            'recentRequests' => Requisition::with(['requester', 'targetProduct'])->latest()->limit(8)->get(),
        ]);
    }
}
