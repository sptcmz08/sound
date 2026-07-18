<?php

namespace App\Services;

use App\Enums\StockDocumentType;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public function next(StockDocumentType $type): string
    {
        $period = now()->format('Ym');
        $prefix = $type->prefix();
        DB::table('document_sequences')->insertOrIgnore(['prefix' => $prefix, 'period' => $period, 'current_number' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $row = DB::table('document_sequences')->where(compact('prefix', 'period'))->lockForUpdate()->first();
        $next = $row->current_number + 1;
        DB::table('document_sequences')->where('id', $row->id)->update(['current_number' => $next, 'updated_at' => now()]);

        return sprintf('%s-%s-%06d', $prefix, $period, $next);
    }
}
