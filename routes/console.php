<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Artisan::command('stock:rebuild-balances {--verify}', function () {
    $totals = DB::table('stock_transactions')->select('product_id', 'warehouse_id', DB::raw('SUM(quantity_in-quantity_out) total'))->groupBy('product_id', 'warehouse_id')->get();
    $errors = 0;
    foreach ($totals as $row) {
        $current = DB::table('stock_balances')->where(['product_id' => $row->product_id, 'warehouse_id' => $row->warehouse_id])->value('quantity') ?? 0;
        if (bccomp((string) $current, (string) $row->total, 4) !== 0) {
            $errors++;
            $this->warn("Mismatch product {$row->product_id}: {$current} / {$row->total}");
            if (! $this->option('verify')) {
                DB::table('stock_balances')->updateOrInsert(['product_id' => $row->product_id, 'warehouse_id' => $row->warehouse_id], ['quantity' => $row->total, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }$this->info($errors ? "พบ {$errors} รายการ" : 'ยอดคงเหลือตรงกับ Transaction');

    return $this->option('verify') && $errors ? 1 : 0;
})->purpose('ตรวจสอบหรือสร้างยอดคงเหลือใหม่จาก Stock Transaction');
