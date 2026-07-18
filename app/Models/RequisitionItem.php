<?php

namespace App\Models;

use App\Casts\FlexibleDecimal;
use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['quantity' => FlexibleDecimal::class];
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
