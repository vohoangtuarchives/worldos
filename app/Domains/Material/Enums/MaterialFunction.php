<?php

namespace App\Domains\Material\Enums;

enum MaterialFunction: string
{
    case LEGITIMIZING = 'legitimizing';
    case STABILIZING = 'stabilizing';
    case TRANSFORMATIVE = 'transformative';
    case DESTRUCTIVE = 'destructive';
    case DESTABILIZING = 'destabilizing';
}
