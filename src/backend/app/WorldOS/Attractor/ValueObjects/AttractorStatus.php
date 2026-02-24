<?php

declare(strict_types=1);

namespace App\WorldOS\Attractor\ValueObjects;

/**
 * Attractor lifecycle status.
 */
enum AttractorStatus: string
{
    case DORMANT = 'dormant';
    case ACTIVE = 'active';
    case CAPTURED = 'captured';
    case ESCAPED = 'escaped';

    public function canActivate(): bool
    {
        return $this === self::DORMANT;
    }

    public function canCapture(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canEscape(): bool
    {
        return $this === self::ACTIVE || $this === self::CAPTURED;
    }

    public function isTerminal(): bool
    {
        return $this === self::ESCAPED;
    }
}
