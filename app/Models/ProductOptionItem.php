<?php

namespace App\Models;

use App\Casts\FlexibleDecimal;
use Illuminate\Database\Eloquent\Model;

class ProductOptionItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'quantity' => FlexibleDecimal::class,
            'additional_price' => FlexibleDecimal::class,
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function group()
    {
        return $this->belongsTo(ProductOptionGroup::class, 'product_option_group_id');
    }

    public function optionProduct()
    {
        return $this->belongsTo(Product::class, 'option_product_id');
    }
}
