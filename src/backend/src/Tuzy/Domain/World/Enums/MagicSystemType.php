<?php

declare(strict_types=1);

namespace Tuzy\Domain\World\Enums;

enum MagicSystemType: string
{
    case NONE = 'NONE';
    case INTERNAL_QI = 'INTERNAL_QI';
    case SPIRITUAL_QI = 'SPIRITUAL_QI';
    case MANA = 'MANA';
    case MIXED = 'MIXED';
}
