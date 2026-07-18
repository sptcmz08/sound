<?php

namespace App\Models;

use App\Casts\FlexibleDecimal;
use App\Enums\RequisitionStatus;
use App\Enums\RequisitionType;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['request_type' => RequisitionType::class, 'status' => RequisitionStatus::class, 'target_quantity' => FlexibleDecimal::class, 'requested_at' => 'datetime', 'requester_signed_at' => 'datetime', 'approved_at' => 'datetime', 'rejected_at' => 'datetime'];
    }

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function targetProduct()
    {
        return $this->belongsTo(Product::class, 'target_product_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function stockDocuments()
    {
        return $this->belongsToMany(StockDocument::class, 'requisition_stock_documents');
    }
}
