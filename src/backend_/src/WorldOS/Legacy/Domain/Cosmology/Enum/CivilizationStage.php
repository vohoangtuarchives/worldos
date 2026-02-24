<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\Enum;

enum CivilizationStage: string
{
    case GENESIS = 'GENESIS';
    case EXPANSION = 'EXPANSION';
    case TENSION = 'TENSION';
    case CRISIS = 'CRISIS';
    case COLLAPSE = 'COLLAPSE';
    case RECONFIGURATION = 'RECONFIGURATION';
    case STABILIZATION = 'STABILIZATION';

    public function label(): string
    {
        return match ($this) {
            self::GENESIS => 'Genesis',
            self::EXPANSION => 'Expansion',
            self::TENSION => 'Tension',
            self::CRISIS => 'Crisis',
            self::COLLAPSE => 'Collapse',
            self::RECONFIGURATION => 'Reconfiguration',
            self::STABILIZATION => 'Stabilization',
        };
    }
}
