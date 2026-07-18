<?php

namespace App\Enums;

enum RequisitionStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'รออนุมัติ', self::APPROVED => 'อนุมัติแล้ว', self::REJECTED => 'ไม่อนุมัติ'
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'badge-amber', self::APPROVED => 'badge-green', self::REJECTED => 'badge-red'
        };
    }
}
