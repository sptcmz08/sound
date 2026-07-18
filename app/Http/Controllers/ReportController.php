<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Services\ProductSpreadsheetService;
use App\Services\StockReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function balances(Request $r, StockReportService $reports)
    {
        $balances = $reports->balances($r->only('q', 'type'))->paginate(30)->withQueryString();

        return view('reports.balances', compact('balances'));
    }

    public function card(Request $r, StockReportService $reports)
    {
        $products = Product::orderBy('code')->get();
        $transactions = $r->product_id ? $reports->card((int) $r->product_id)->paginate(50)->withQueryString() : null;

        return view('reports.card', compact('products', 'transactions'));
    }

    public function movements(Request $r)
    {
        $rows = StockTransaction::with(['product', 'document', 'user'])->when($r->date_from, fn ($q, $v) => $q->whereDate('occurred_at', '>=', $v))->when($r->date_to, fn ($q, $v) => $q->whereDate('occurred_at', '<=', $v))->latest('occurred_at')->paginate(50)->withQueryString();

        return view('reports.movements', compact('rows'));
    }

    public function export(Request $r, StockReportService $reports, ProductSpreadsheetService $spreadsheets)
    {
        $rows = $reports->balances($r->only('q', 'type'))->get();

        return response()->streamDownload(function () use ($rows, $spreadsheets) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['รหัส', 'Barcode', 'ชื่อ', 'ประเภท', 'คลัง', 'คงเหลือ', 'หน่วย']);
            foreach ($rows as $b) {
                fputcsv($out, array_map($spreadsheets->escapeCsvCell(...), [$b->product->code, $b->product->barcode, $b->product->name, $b->product->product_type->value, $b->warehouse->name, $b->quantity, $b->product->unit->name]));
            }fclose($out);
        }, 'stock-balances-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportExcel(Request $request, StockReportService $reports, ProductSpreadsheetService $spreadsheets)
    {
        $rows = $reports->balances($request->only('q', 'type'))->get();
        $path = $spreadsheets->writeBalanceWorkbook($rows);

        return response()->download($path, 'stock-balances-'.now()->format('Ymd-His').'.xlsx')->deleteFileAfterSend(true);
    }

    public function audits()
    {
        abort_unless(request()->user()->isAdmin(), 403);

        return view('reports.audits', ['rows' => AuditLog::with('user')->latest()->paginate(50)]);
    }
}
