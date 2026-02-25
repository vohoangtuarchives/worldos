<?php

declare(strict_types=1);

namespace App\Modules\Universe\ValueObjects;

/**
 * Universe Status — lifecycle state of a Universe runtime instance.
 *
 *   RUNNING   — Actively evolving via tick
 *   PAUSED    — Temporarily suspended
 *   COLLAPSED — Universe has reached critical failure
 *   ARCHIVED  — Permanently stored for reference
 */
enum UniverseStatus: string
{
    case RUNNING = 'RUNNING';
    case PAUSED = 'PAUSED';
    case COLLAPSED = 'COLLAPSED';
    case ARCHIVED = 'ARCHIVED';

    public function canTick(): bool
    {
        return $this === self::RUNNING;
    }

    public function canPause(): bool
    {
        return $this === self::RUNNING;
    }

    public function canResume(): bool
    {
        return $this === self::PAUSED;
    }

    public function canCollapse(): bool
    {
        return $this === self::RUNNING || $this === self::PAUSED;
    }

    public function canArchive(): bool
    {
        return $this !== self::ARCHIVED;
    }

    public function canFork(): bool
    {
        return $this === self::RUNNING || $this === self::PAUSED;
    }

    public function isTerminal(): bool
    {
        return $this === self::COLLAPSED || $this === self::ARCHIVED;
    }
}
