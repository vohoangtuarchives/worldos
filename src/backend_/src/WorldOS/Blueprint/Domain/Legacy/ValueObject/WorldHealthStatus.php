<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\ValueObject;

enum WorldHealthStatus: string
{
    case STABLE = 'STABLE';
    case DEGRADED = 'DEGRADED';
    case CRITICAL = 'CRITICAL';
    case HALTED = 'HALTED';

    public function color(): string
    {
        return match ($this) {
            self::STABLE => 'success',
            self::DEGRADED => 'warning',
            self::CRITICAL => 'danger',
            self::HALTED => 'dark',
        };
    }
}
