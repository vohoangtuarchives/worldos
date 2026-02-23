<?php

declare(strict_types=1);

namespace WorldOS\Core\ValueObject;

/**
 * SimulationResult: The outcome of one kernel execution.
 */
readonly class SimulationResult
{
    public function __construct(
        public CivilizationSnapshot $snapshot,
        public array $emittedEvents = []
    ) {
    }
}
