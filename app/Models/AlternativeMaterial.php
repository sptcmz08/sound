<?php

namespace App\Models;

use App\Casts\FlexibleDecimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlternativeMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'primary_product_id',
        'alternative_product_id',
        'conversion_factor',
        'note',
    ];

    protected $casts = [
        'conversion_factor' => FlexibleDecimal::class,
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function primaryProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'primary_product_id');
    }

    public function alternativeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'alternative_product_id');
    }
}
