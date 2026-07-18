<?php

namespace App\Enums;

enum StockTransactionType: string
{
    case IN = 'IN';
    case OUT = 'OUT';
    case ADJUST_IN = 'ADJUST_IN';
    case ADJUST_OUT = 'ADJUST_OUT';
    case REVERSAL_IN = 'REVERSAL_IN';
    case REVERSAL_OUT = 'REVERSAL_OUT';
}
