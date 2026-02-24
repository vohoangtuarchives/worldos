<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Application\AdvanceTick;

/**
 * Command to advance a Universe by one simulation tick.
 */
final class AdvanceTickCommand
{
    public function __construct(
        public readonly string $universeId,
        public readonly int    $seed,
        public readonly array  $criticalThresholds = [],
        public readonly float  $forkThreshold = 0.75
    ) {
    }
}
