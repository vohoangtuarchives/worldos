<?php

declare(strict_types=1);

namespace App\WorldOS\World\ValueObjects;

/**
 * World Status — lifecycle state of a World blueprint.
 *
 *   ACTIVE  — World can spawn new Universes
 *   HALTED  — Temporarily suspended, no new spawns
 *   DEAD    — Permanently terminated
 */
enum WorldStatus: string
{
    case ACTIVE = 'ACTIVE';
    case HALTED = 'HALTED';
    case DEAD = 'DEAD';

    public function canSpawnUniverse(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canHalt(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canKill(): bool
    {
        return $this !== self::DEAD;
    }

    public function canResume(): bool
    {
        return $this === self::HALTED;
    }
}
