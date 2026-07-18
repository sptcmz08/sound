<?php

namespace App\Models;

use App\Casts\FlexibleDecimal;
use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['quantity' => FlexibleDecimal::class];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
