<?php

namespace App\Domains\World\Enums;

enum SeedState: string
{
    case DORMANT = 'DORMANT';
    case ACTIVE = 'ACTIVE';
    case EXHAUSTED = 'EXHAUSTED';

    public function color(): string
    {
        return match($this) {
            self::DORMANT => 'secondary',
            self::ACTIVE => 'success',
            self::EXHAUSTED => 'dark',
        };
    }

    public function label(): string
    {
        return match($this) {
            self::DORMANT => '⏸️ Dormant',
            self::ACTIVE => '▶️ Active',
            self::EXHAUSTED => '⏹️ Exhausted',
        };
    }
}
