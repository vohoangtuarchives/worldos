<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Enums;

enum PowerCeiling: string
{
    case HUMAN = 'HUMAN';
    case HUMAN_PLUS = 'HUMAN_PLUS';
    case TRANSCENDENT = 'TRANSCENDENT';
    case IMMORTAL = 'IMMORTAL';

    public function label(): string
    {
        return match ($this) {
            self::HUMAN => 'Human',
            self::HUMAN_PLUS => 'Human Plus',
            self::TRANSCENDENT => 'Transcendent',
            self::IMMORTAL => 'Immortal',
        };
    }
}
