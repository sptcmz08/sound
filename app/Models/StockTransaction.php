<?php

namespace App\Models;

use App\Casts\FlexibleDecimal;
use App\Enums\StockTransactionType;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['transaction_type' => StockTransactionType::class, 'quantity_in' => FlexibleDecimal::class, 'quantity_out' => FlexibleDecimal::class, 'balance_after' => FlexibleDecimal::class, 'occurred_at' => 'datetime', 'created_at' => 'datetime'];
    }

    public function document()
    {
        return $this->belongsTo(StockDocument::class, 'stock_document_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
