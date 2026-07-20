<?php

namespace App\Models;

use App\Casts\FlexibleDecimal;
use Illuminate\Database\Eloquent\Model;

class SaleItemOption extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'quantity' => FlexibleDecimal::class,
        ];
    }

    public function documentItem()
    {
        return $this->belongsTo(StockDocumentItem::class, 'stock_document_item_id');
    }

    public function optionItem()
    {
        return $this->belongsTo(ProductOptionItem::class, 'product_option_item_id');
    }
}
