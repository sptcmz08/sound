<?php

namespace App\Enums;

enum RequisitionType: string
{
    case GENERAL_ISSUE = 'GENERAL_ISSUE';
    case ISSUE_PART = 'ISSUE_PART';
    case ISSUE_SUPPLY = 'ISSUE_SUPPLY';
    case BUILD_WIP = 'BUILD_WIP';
    case BUILD_FG = 'BUILD_FG';
    case ISSUE_WIP = 'ISSUE_WIP';
    case ISSUE_FG = 'ISSUE_FG';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL_ISSUE => 'เบิก PART / SUPPLY (รายการเดิม)', self::ISSUE_PART => 'เบิก PART', self::ISSUE_SUPPLY => 'เบิก SUPPLY', self::BUILD_WIP => 'ผลิต WIP', self::BUILD_FG => 'ผลิต FG', self::ISSUE_WIP => 'เบิก WIP', self::ISSUE_FG => 'เบิก FG'
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::GENERAL_ISSUE => 'รายการเบิก PART หรือ SUPPLY ที่สร้างจากระบบรุ่นเดิม', self::ISSUE_PART => 'เบิกอะไหล่ PART ที่กำหนดจำนวนได้', self::ISSUE_SUPPLY => 'เบิกวัสดุสิ้นเปลือง SUPPLY แยกจากอะไหล่', self::BUILD_WIP => 'ตัด PART ตามสูตรและเพิ่ม WIP เข้าสต็อก', self::BUILD_FG => 'ตัด WIP + PART ตามสูตรและเพิ่ม FG เข้าสต็อก', self::ISSUE_WIP => 'เบิก WIP ออกจากสต็อก', self::ISSUE_FG => 'เบิก FG ออกจากสต็อก'
        };
    }

    public function isBuild(): bool
    {
        return in_array($this, [self::BUILD_WIP, self::BUILD_FG], true);
    }
}
