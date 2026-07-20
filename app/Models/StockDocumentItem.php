<?php

namespace App\Models;

use App\Casts\FlexibleDecimal;
use Illuminate\Database\Eloquent\Model;

class StockDocumentItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['quantity' => FlexibleDecimal::class, 'unit_cost' => FlexibleDecimal::class, 'unit_price' => FlexibleDecimal::class];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function document()
    {
        return $this->belongsTo(StockDocument::class, 'stock_document_id');
    }

    public function options()
    {
        return $this->hasMany(SaleItemOption::class, 'stock_document_item_id');
    }
}
