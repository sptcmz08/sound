<?php

namespace App\Enums;

enum StockDocumentStatus: string
{
    case DRAFT = 'DRAFT';
    case POSTED = 'POSTED';
    case CANCELLED = 'CANCELLED';
}
