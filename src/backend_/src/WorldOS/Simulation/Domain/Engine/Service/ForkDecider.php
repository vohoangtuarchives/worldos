<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Domain\Engine\Service;

use WorldOS\Simulation\Domain\Engine\ValueObject\TickResult;

/**
 * ForkDecider: Determines if a Timeline should branch (DAG fork) based on the tick result.
 *
 * Branching occurs when total anomaly score exceeds the fork threshold.
 * This creates a new child Universe from the current state.
 */
final class ForkDecider
{
    /**
     * Default threshold if not configured in the CompiledPolicy DSL.
     */
    private const DEFAULT_FORK_THRESHOLD = 0.75;

    public function __construct(
        private readonly float $forkThreshold = self::DEFAULT_FORK_THRESHOLD
    ) {
    }

    /**
     * Returns true if the TickResult's anomaly score justifies a Timeline fork.
     */
    public function shouldFork(TickResult $result): bool
    {
        return $result->totalAnomalyScore() >= $this->forkThreshold;
    }

    /**
     * Returns a percentage score of how close we are to forking (0.0-1.0).
     * Useful for UI tension indicators.
     */
    public function forkPressure(TickResult $result): float
    {
        return min(1.0, $result->totalAnomalyScore() / $this->forkThreshold);
    }
}
