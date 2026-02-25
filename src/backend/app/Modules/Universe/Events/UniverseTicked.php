<?php

declare(strict_types=1);

namespace App\Modules\Universe\Events;

use App\Modules\Universe\ValueObjects\UniverseId;
use App\Modules\Shared\ValueObjects\WorldStateVector;

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
