<?php

namespace App\Enums;

enum ProductType: string
{
    case PART = 'PART';
    case SUPPLY = 'SUPPLY';
    case WIP = 'WIP';
    case FG = 'FG';

    public function label(): string
    {
        return match ($this) {
            self::PART => 'PART (อะไหล่)',
            self::SUPPLY => 'SUPPLY (วัสดุสิ้นเปลือง)',
            self::WIP => 'WIP (งานระหว่างประกอบ)',
            self::FG => 'FG (สินค้าสำเร็จรูป)'
        };
    }
}
