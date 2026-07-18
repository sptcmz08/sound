<?php

namespace App\Models;

use App\Enums\StockDocumentStatus;
use App\Enums\StockDocumentType;
use Illuminate\Database\Eloquent\Model;

class StockDocument extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['document_type' => StockDocumentType::class, 'status' => StockDocumentStatus::class, 'document_date' => 'date', 'posted_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function items()
    {
        return $this->hasMany(StockDocumentItem::class);
    }

    public function transactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reversal()
    {
        return $this->hasOne(self::class, 'reversal_of_id');
    }
}
