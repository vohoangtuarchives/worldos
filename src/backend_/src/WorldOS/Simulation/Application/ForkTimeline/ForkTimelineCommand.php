<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Application\ForkTimeline;

/**
 * Command to fork a Timeline (DAG branch) from a Universe's current state.
 */
final class ForkTimelineCommand
{
    public function __construct(
        public readonly string $parentUniverseId,
        public readonly string $multiverseId,
        public readonly int    $forkSeed,
        public readonly string $forkReason = 'anomaly_spike'
    ) {
    }
}
