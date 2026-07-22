<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockDocument;
use App\Models\StockDocumentItem;
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

    public function costProfit(Request $request)
    {
        $base = StockDocumentItem::query()
            ->join('stock_documents', 'stock_documents.id', '=', 'stock_document_items.stock_document_id')
            ->where('stock_documents.document_type', 'SALE_OUT')
            ->where('stock_documents.status', 'POSTED')
            ->when($request->date_from, fn ($query, $value) => $query->whereDate('stock_documents.document_date', '>=', $value))
            ->when($request->date_to, fn ($query, $value) => $query->whereDate('stock_documents.document_date', '<=', $value));
        $totals = (clone $base)->selectRaw('COALESCE(SUM(stock_document_items.quantity * stock_document_items.unit_price),0) revenue, COALESCE(SUM(stock_document_items.quantity * stock_document_items.unit_cost),0) cost')->first();
        $rows = $base->with(['product', 'document'])->select('stock_document_items.*')->latest('stock_documents.document_date')->paginate(40)->withQueryString();

        return view('reports.cost-profit', compact('rows', 'totals'));
    }

    public function issues(Request $request)
    {
        return $this->documentReport($request, ['PART_OUT', 'SUPPLY_OUT', 'WIP_OUT', 'FG_OUT'], 'รายงานเบิก - จ่าย', 'รายการเบิก PART, SUPPLY, WIP และ FG ออกจากสต็อก');
    }

    public function sales(Request $request)
    {
        return $this->documentReport($request, ['SALE_OUT'], 'รายงานขาย', 'รายการขาย FG และมูลค่าขาย');
    }

    public function claims(Request $request)
    {
        return $this->documentReport($request, ['CLAIM_IN'], 'รายงานเคลมจากลูกค้า', 'สินค้าที่ลูกค้าส่งเคลมกลับเข้าสต็อก');
    }

    public function waste(Request $request)
    {
        return $this->documentReport($request, ['WASTE_OUT'], 'รายงานของเสีย', 'ของเสียจากลูกค้าและกระบวนการผลิต');
    }

    private function documentReport(Request $request, array $types, string $title, string $subtitle)
    {
        $rows = StockDocument::with(['items.product.unit', 'warehouse', 'creator'])
            ->whereIn('document_type', $types)
            ->where('status', 'POSTED')
            ->when($request->date_from, fn ($query, $value) => $query->whereDate('document_date', '>=', $value))
            ->when($request->date_to, fn ($query, $value) => $query->whereDate('document_date', '<=', $value))
            ->latest('document_date')->latest('id')->paginate(30)->withQueryString();

        return view('reports.documents', compact('rows', 'title', 'subtitle'));
    }
}
