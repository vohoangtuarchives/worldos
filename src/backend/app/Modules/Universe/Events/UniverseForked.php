<?php

declare(strict_types=1);

namespace App\Modules\Universe\Events;

use App\Modules\Universe\ValueObjects\UniverseId;

/**
 * Domain Event: Universe has been forked at a specific tick.
 */
final readonly class UniverseForked
{
    public function __construct(
        public UniverseId $childUniverseId,
        public UniverseId $parentUniverseId,
        public int $forkTick,
    ) {
    }
}
