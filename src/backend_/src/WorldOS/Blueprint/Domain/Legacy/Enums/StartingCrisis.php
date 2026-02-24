<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Enums;

enum StartingCrisis: string
{
    case NONE = 'NONE';
    case WAR = 'WAR';
    case PLAGUE = 'PLAGUE';
    case INVASION = 'INVASION';
    case FAMINE = 'FAMINE';
    case AWAKENING = 'AWAKENING';

    public function label(): string
    {
        return match ($this) {
            self::NONE => 'None',
            self::WAR => 'War',
            self::PLAGUE => 'Plague',
            self::INVASION => 'Invasion',
            self::FAMINE => 'Famine',
            self::AWAKENING => 'Awakening',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::NONE => 'Peace',
            self::WAR => 'War',
            self::PLAGUE => 'Plague',
            self::INVASION => 'Invasion',
            self::FAMINE => 'Famine',
            self::AWAKENING => 'Awakening',
        };
    }
}
