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
            self::PART => 'PART', self::WIP => 'WIP', self::FG => 'FG'
        };
    }
}
