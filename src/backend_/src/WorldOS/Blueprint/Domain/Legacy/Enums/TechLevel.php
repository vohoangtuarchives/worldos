<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Enums;

enum TechLevel: string
{
    case PRIMITIVE = 'PRIMITIVE';
    case MEDIEVAL = 'MEDIEVAL';
    case DYNASTIC = 'DYNASTIC';
    case EARLY_INDUSTRIAL = 'EARLY_INDUSTRIAL';
    case MODERN = 'MODERN';
    case FUTURISTIC = 'FUTURISTIC';

    public function label(): string
    {
        return match ($this) {
            self::PRIMITIVE => 'Primitive',
            self::MEDIEVAL => 'Medieval',
            self::DYNASTIC => 'Dynastic',
            self::EARLY_INDUSTRIAL => 'Early Industrial',
            self::MODERN => 'Modern',
            self::FUTURISTIC => 'Futuristic',
        };
    }
}
