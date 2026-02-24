<?php

declare(strict_types=1);

namespace App\WorldOS\CivilizationMemory\Events;

use App\WorldOS\CivilizationMemory\ValueObjects\MythId;
use App\WorldOS\Runtime\ValueObjects\UniverseId;

/**
 * Domain Event: A new Myth has emerged from crystallized belief.
 */
final readonly class MythEmerged
{
    public function __construct(
        public MythId $mythId,
        public UniverseId $universeId,
        public string $theme,
        public float $strength,
        public int $tick,
    ) {
    }
}
