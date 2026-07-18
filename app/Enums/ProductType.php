<?php

namespace App\Enums;

enum ProductType: string
{
    case PART = 'PART';
    case WIP = 'WIP';
    case FG = 'FG';

    public function label(): string
    {
        return match ($this) {
            self::PART => 'อะไหล่ทั่วไป', self::WIP => 'วิช', self::FG => 'สินค้าสำเร็จรูป (FG)'
        };
    }
}
