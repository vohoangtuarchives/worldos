<?php

namespace App\Domains\World\Enums;

enum WorldHealthStatus: string
{
    case STABLE = 'STABLE';
    case DEGRADED = 'DEGRADED';
    case CRITICAL = 'CRITICAL';
    case HALTED = 'HALTED';

    public function color(): string
    {
        return match($this) {
            self::STABLE => 'success',
            self::DEGRADED => 'warning',
            self::CRITICAL => 'danger',
            self::HALTED => 'dark',
        };
    }
}
