<?php

namespace App\Models;

use App\Casts\FlexibleDecimal;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['product_type' => ProductType::class, 'minimum_stock' => FlexibleDecimal::class, 'is_active' => 'boolean'];
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function balances()
    {
        return $this->hasMany(StockBalance::class);
    }

    public function transactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function components()
    {
        return $this->belongsToMany(self::class, 'product_components', 'parent_product_id', 'component_product_id')->withPivot('quantity')->withTimestamps();
    }

    public function usedInProducts()
    {
        return $this->belongsToMany(self::class, 'product_components', 'component_product_id', 'parent_product_id')->withPivot('quantity')->withTimestamps();
    }
}
