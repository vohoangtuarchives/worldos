<?php

declare(strict_types=1);

namespace Tuzy\Domain\Material\Enums;

enum MaterialFunction: string
{
    case LEGITIMIZING = 'legitimizing';
    case STABILIZING = 'stabilizing';
    case TRANSFORMATIVE = 'transformative';
    case DESTRUCTIVE = 'destructive';
    case DESTABILIZING = 'destabilizing';
    case RECOVERY = 'recovery';
    case WEAPON = 'weapon';
    case AUGMENTATION = 'augmentation';
    case UNKNOWN = 'unknown';
}
