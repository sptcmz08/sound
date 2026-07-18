<?php

namespace App\Enums;

enum StockDocumentType: string
{
    case PART_IN = 'PART_IN';
    case PART_OUT = 'PART_OUT';
    case WIP_IN = 'WIP_IN';
    case WIP_OUT = 'WIP_OUT';
    case FG_IN = 'FG_IN';
    case FG_OUT = 'FG_OUT';
    case ADJUST_IN = 'ADJUST_IN';
    case ADJUST_OUT = 'ADJUST_OUT';
    case REVERSAL = 'REVERSAL';

    public function isInbound(): bool
    {
        return in_array($this, [self::PART_IN, self::WIP_IN, self::FG_IN, self::ADJUST_IN], true);
    }

    public function productType(): ?ProductType
    {
        return match ($this) {
            self::PART_IN,self::PART_OUT => ProductType::PART,self::WIP_IN,self::WIP_OUT => ProductType::WIP,self::FG_IN,self::FG_OUT => ProductType::FG,default => null
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::PART_IN => 'PIN',self::PART_OUT => 'POUT',self::WIP_IN => 'WIN',self::WIP_OUT => 'WOUT',self::FG_IN => 'FGIN',self::FG_OUT => 'FGOUT',self::ADJUST_IN => 'ADJIN',self::ADJUST_OUT => 'ADJOUT',self::REVERSAL => 'REV'
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PART_IN => 'รับอะไหล่',self::PART_OUT => 'จ่ายอะไหล่',self::WIP_IN => 'สร้างวิช',self::WIP_OUT => 'จ่ายวิช',self::FG_IN => 'สร้าง FG',self::FG_OUT => 'จ่าย FG',self::ADJUST_IN => 'ปรับเพิ่ม',self::ADJUST_OUT => 'ปรับลด',self::REVERSAL => 'ย้อนรายการ'
        };
    }
}
