<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessOperationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\StockDocumentController;
use App\Http\Controllers\StockReceiptController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────
// Dev-Tools: /dev-tools?key=sound2026!
// ──────────────────────────────────────────────
if (app()->environment('local') || request()->query('key') === 'sound2026!' || request()->header('X-Dev-Key') === 'sound2026!') {
    Route::prefix('dev-tools')->group(function () {
        $guard = function (Request $request) {
            if ($request->query('key') !== 'sound2026!') {
                abort(403, 'Invalid key');
            }
        };

        Route::get('/', function (Request $request) use ($guard) {
            $guard($request);

            return view('dev-tools', ['key' => $request->query('key')]);
        });

        Route::post('/migrate', function (Request $request) use ($guard) {
            $guard($request);
            Artisan::call('migrate', ['--force' => true]);

            return back()->with('result', '✅ migrate --force'."\n".Artisan::output());
        });

        Route::post('/optimize', function (Request $request) use ($guard) {
            $guard($request);
            Artisan::call('optimize:clear');
            $out1 = Artisan::output();
            Artisan::call('optimize');
            $out2 = Artisan::output();

            return back()->with('result', '✅ optimize:clear + optimize'."\n".$out1.$out2);
        });

        Route::post('/seed', function (Request $request) use ($guard) {
            $guard($request);
            Artisan::call('db:seed', ['--force' => true]);

            return back()->with('result', '✅ db:seed --force'."\n".Artisan::output());
        });

        Route::post('/migrate-fresh', function (Request $request) use ($guard) {
            $guard($request);
            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);

            return back()->with('result', '⚠️ migrate:fresh --seed'."\n".Artisan::output());
        });

        Route::post('/custom', function (Request $request) use ($guard) {
            $guard($request);
            $cmd = $request->input('command');
            if (! $cmd) {
                return back()->with('result', '❌ ไม่ได้ระบุคำสั่ง');
            }
            try {
                Artisan::call($cmd);

                return back()->with('result', "✅ $cmd\n".Artisan::output());
            } catch (Throwable $e) {
                return back()->with('result', "❌ $cmd\n".$e->getMessage());
            }
        });
    });
}

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}/image', [ProductController::class, 'image'])->name('products.image');
    Route::get('/requisitions', [RequisitionController::class, 'index'])->name('requisitions.index');
    Route::middleware('role:ADMIN,STOCK_STAFF')->group(function () {
        Route::get('/operations/{operation}', [BusinessOperationController::class, 'create'])->whereIn('operation', ['supplier-receive', 'sale', 'claim', 'waste'])->name('operations.create');
        Route::post('/operations/{operation}', [BusinessOperationController::class, 'store'])->whereIn('operation', ['supplier-receive', 'sale', 'claim', 'waste'])->name('operations.store');
        Route::get('/withdraw', [RequisitionController::class, 'withdraw'])->name('requisitions.withdraw');
        Route::get('/production', [RequisitionController::class, 'production'])->name('requisitions.production');
        Route::get('/production/wip/create', [RequisitionController::class, 'createWip'])->name('requisitions.wip.create');
        Route::post('/production/wip', [RequisitionController::class, 'storeWip'])->name('requisitions.wip.store');
        Route::get('/requisitions/create', [RequisitionController::class, 'create'])->name('requisitions.create');
        Route::post('/requisitions', [RequisitionController::class, 'store'])->name('requisitions.store');
    });
    Route::get('/requisitions/{requisition}', [RequisitionController::class, 'show'])->name('requisitions.show');
    Route::post('/requisitions/{requisition}/confirm', [RequisitionController::class, 'confirm'])->name('requisitions.confirm');
    Route::get('/requisitions/{requisition}/print', [RequisitionController::class, 'print'])->name('requisitions.print');
    Route::get('/requisitions/{requisition}/pdf', [RequisitionController::class, 'pdf'])->name('requisitions.pdf');
    Route::post('/requisitions/{requisition}/sign', [RequisitionController::class, 'sign'])->name('requisitions.sign');
    Route::get('/requisitions/{requisition}/requester-signature', [RequisitionController::class, 'signature'])->name('requisitions.signature');
    Route::get('/my-signature', [SignatureController::class, 'edit'])->name('signature.edit');
    Route::post('/my-signature', [SignatureController::class, 'update'])->name('signature.update');
    Route::get('/my-signature/{signature}', [SignatureController::class, 'show'])->name('signature.show');
    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/products/{product}/quick-image', [ProductController::class, 'quickImage'])->name('products.quick-image');
        Route::resource('products', ProductController::class)->except(['index', 'show']);
        Route::get('/stock/receive', [StockReceiptController::class, 'create'])->name('stock.receive');
        Route::post('/stock/receive', [StockReceiptController::class, 'store'])->name('stock.receive.store');
        Route::get('/approvals', [RequisitionController::class, 'approvals'])->name('requisitions.approvals');
        Route::get('/issues', [RequisitionController::class, 'issues'])->name('requisitions.issues');
        Route::post('/requisitions/{requisition}/approve', [RequisitionController::class, 'approve'])->name('requisitions.approve');
        Route::post('/requisitions/{requisition}/reject', [RequisitionController::class, 'reject'])->name('requisitions.reject');
        Route::get('/products-import', [ProductImportController::class, 'form'])->name('products.import.form');
        Route::post('/products-import', [ProductImportController::class, 'import'])->name('products.import');
        Route::get('/products-import/template', [ProductImportController::class, 'template'])->name('products.import.template');
        Route::get('/settings', [MasterDataController::class, 'index'])->name('settings');
        Route::post('/settings/units', [MasterDataController::class, 'unit'])->name('settings.units');
        Route::put('/settings/units/{unit}', [MasterDataController::class, 'updateUnit'])->name('settings.units.update');
        Route::delete('/settings/units/{unit}', [MasterDataController::class, 'destroyUnit'])->name('settings.units.destroy');
        Route::post('/settings/warehouses', [MasterDataController::class, 'warehouse'])->name('settings.warehouses');
        Route::put('/settings/warehouses/{warehouse}', [MasterDataController::class, 'updateWarehouse'])->name('settings.warehouses.update');
        Route::delete('/settings/warehouses/{warehouse}', [MasterDataController::class, 'destroyWarehouse'])->name('settings.warehouses.destroy');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/documents/{document}/cancel', [StockDocumentController::class, 'cancel'])->name('documents.cancel');
        Route::get('/audit-logs', [ReportController::class, 'audits'])->name('audits');
    });
    Route::middleware('role:ADMIN,STOCK_STAFF')->group(function () {
        Route::get('/documents/{type}', [StockDocumentController::class, 'index'])->whereIn('type', ['part_in', 'part_out', 'fg_in', 'fg_out', 'adjust_in', 'adjust_out'])->name('documents.index');
        Route::get('/documents/{type}/create', [StockDocumentController::class, 'create'])->whereIn('type', ['part_in', 'part_out', 'fg_in', 'fg_out', 'adjust_in', 'adjust_out'])->name('documents.create');
        Route::post('/documents/{type}', [StockDocumentController::class, 'store'])->whereIn('type', ['part_in', 'part_out', 'fg_in', 'fg_out', 'adjust_in', 'adjust_out'])->name('documents.store');
    });
    Route::get('/document/{document}', [StockDocumentController::class, 'show'])->name('documents.show');
    Route::get('/reports/balances', [ReportController::class, 'balances'])->name('reports.balances');
    Route::get('/reports/balances/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/balances/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/stock-card', [ReportController::class, 'card'])->name('reports.card');
    Route::get('/reports/movements', [ReportController::class, 'movements'])->name('reports.movements');
    Route::get('/reports/cost-profit', [ReportController::class, 'costProfit'])->name('reports.cost-profit');
    Route::get('/reports/issue', [ReportController::class, 'issues'])->name('reports.issue');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/claims', [ReportController::class, 'claims'])->name('reports.claims');
    Route::get('/reports/waste', [ReportController::class, 'waste'])->name('reports.waste');
});
