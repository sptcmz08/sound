<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Services\ProductSpreadsheetService;
use Illuminate\Http\Request;

class ProductImportController extends Controller
{
    public function form()
    {
        return view('products.import');
    }

    public function import(Request $request, AuditLogService $audit, ProductSpreadsheetService $spreadsheets)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240']]);
        $count = $spreadsheets->import($request->file('file')->getRealPath(), $request->user());
        $audit->record($request->user(), 'IMPORT', 'product', null, null, [
            'count' => $count,
            'filename' => $request->file('file')->getClientOriginalName(),
        ]);

        return redirect()->route('products.index')->with('success', "นำเข้า {$count} รายการแล้ว");
    }

    public function template(ProductSpreadsheetService $spreadsheets)
    {
        $path = $spreadsheets->writeImportTemplate();

        return response()->download($path, 'product-import-template.xlsx')->deleteFileAfterSend(true);
    }
}
