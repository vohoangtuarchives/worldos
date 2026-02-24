<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\Events;

use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * Domain Event: Universe has advanced one tick.
 */
final readonly class UniverseTicked
{
    public function __construct(
        public UniverseId $universeId,
        public int $tick,
        public WorldStateVector $stateVector,
        public float $stabilityValue,
    ) {
    }
}
