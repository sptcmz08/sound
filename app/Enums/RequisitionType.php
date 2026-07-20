<?php

namespace App\Enums;

enum RequisitionType: string
{
    case GENERAL_ISSUE = 'GENERAL_ISSUE';
    case BUILD_WIP = 'BUILD_WIP';
    case BUILD_FG = 'BUILD_FG';
    case ISSUE_WIP = 'ISSUE_WIP';
    case ISSUE_FG = 'ISSUE_FG';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL_ISSUE => 'เบิก PART', self::BUILD_WIP => 'ผลิต WIP', self::BUILD_FG => 'ผลิต FG', self::ISSUE_WIP => 'เบิก WIP', self::ISSUE_FG => 'เบิก FG'
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::GENERAL_ISSUE => 'เบิก PART ออกจากสต็อก', self::BUILD_WIP => 'ตัด PART ตามสูตรและเพิ่ม WIP เข้าสต็อก', self::BUILD_FG => 'ตัด WIP/PART ตามสูตรและเพิ่ม FG เข้าสต็อก', self::ISSUE_WIP => 'เบิก WIP ออกจากสต็อก', self::ISSUE_FG => 'เบิก FG ออกจากสต็อก'
        };
    }

    public function isBuild(): bool
    {
        return in_array($this, [self::BUILD_WIP, self::BUILD_FG], true);
    }
}
