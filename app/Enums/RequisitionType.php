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
            self::GENERAL_ISSUE => 'เบิกอะไหล่ทั่วไป', self::BUILD_WIP => 'สร้างวิช', self::BUILD_FG => 'สร้างสินค้าสำเร็จรูป (FG)', self::ISSUE_WIP => 'เบิกวิช', self::ISSUE_FG => 'เบิก FG เพื่อขาย/ส่งมอบ'
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::GENERAL_ISSUE => 'เบิกอะไหล่ออกไปใช้', self::BUILD_WIP => 'ตัดอะไหล่ตามสูตร และเพิ่มวิช', self::BUILD_FG => 'ตัดวิช/อะไหล่ และเพิ่ม FG', self::ISSUE_WIP => 'เบิกวิชออกไปใช้', self::ISSUE_FG => 'เบิก FG ออกไปขาย'
        };
    }

    public function isBuild(): bool
    {
        return in_array($this, [self::BUILD_WIP, self::BUILD_FG], true);
    }
}
