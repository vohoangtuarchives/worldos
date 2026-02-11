<?php

namespace App\Domains\World\Enums;

enum MagicSystemType: string
{
    case NONE = 'NONE';
    case INTERNAL_QI = 'INTERNAL_QI'; // Kiếm hiệp
    case SPIRITUAL_QI = 'SPIRITUAL_QI'; // Tiên hiệp
    case MANA = 'MANA'; // Western Fantasy
    case MIXED = 'MIXED';
}
