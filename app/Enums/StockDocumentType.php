<?php

namespace App\Enums;

enum StockDocumentType: string
{
    case PART_IN = 'PART_IN';
    case PART_OUT = 'PART_OUT';
    case SUPPLY_IN = 'SUPPLY_IN';
    case SUPPLY_OUT = 'SUPPLY_OUT';
    case WIP_IN = 'WIP_IN';
    case WIP_OUT = 'WIP_OUT';
    case FG_IN = 'FG_IN';
    case FG_OUT = 'FG_OUT';
    case ADJUST_IN = 'ADJUST_IN';
    case ADJUST_OUT = 'ADJUST_OUT';
    case SUPPLIER_IN = 'SUPPLIER_IN';
    case SALE_OUT = 'SALE_OUT';
    case CLAIM_IN = 'CLAIM_IN';
    case WASTE_OUT = 'WASTE_OUT';
    case REVERSAL = 'REVERSAL';

    public function isInbound(): bool
    {
        return in_array($this, [self::PART_IN, self::SUPPLY_IN, self::WIP_IN, self::FG_IN, self::ADJUST_IN, self::SUPPLIER_IN, self::CLAIM_IN], true);
    }

    public function productType(): ?ProductType
    {
        return match ($this) {
            self::PART_IN,self::PART_OUT => ProductType::PART,
            self::SUPPLY_IN,self::SUPPLY_OUT => ProductType::SUPPLY,
            self::WIP_IN,self::WIP_OUT => ProductType::WIP,
            self::FG_IN,self::FG_OUT => ProductType::FG,
            default => null
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::PART_IN => 'PIN',self::PART_OUT => 'POUT',
            self::SUPPLY_IN => 'SIN',self::SUPPLY_OUT => 'SOUT',
            self::WIP_IN => 'WIN',self::WIP_OUT => 'WOUT',
            self::FG_IN => 'FGIN',self::FG_OUT => 'FGOUT',
            self::ADJUST_IN => 'ADJIN',self::ADJUST_OUT => 'ADJOUT',
            self::SUPPLIER_IN => 'SUP',self::SALE_OUT => 'SALE',
            self::CLAIM_IN => 'CLM',self::WASTE_OUT => 'WST',self::REVERSAL => 'REV'
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PART_IN => 'รับ PART',self::PART_OUT => 'จ่าย PART',
            self::SUPPLY_IN => 'รับ SUPPLY',self::SUPPLY_OUT => 'จ่าย SUPPLY',
            self::WIP_IN => 'ผลิต WIP เข้าสต็อก',self::WIP_OUT => 'จ่าย WIP',
            self::FG_IN => 'ผลิต FG เข้าสต็อก',self::FG_OUT => 'จ่าย FG',
            self::ADJUST_IN => 'ปรับเพิ่ม',self::ADJUST_OUT => 'ปรับลด',
            self::SUPPLIER_IN => 'รับเข้าจาก Supplier',self::SALE_OUT => 'ขาย',
            self::CLAIM_IN => 'รับเคลมจากลูกค้า',self::WASTE_OUT => 'ของเสีย',
            self::REVERSAL => 'ย้อนรายการ'
        };
    }
}
