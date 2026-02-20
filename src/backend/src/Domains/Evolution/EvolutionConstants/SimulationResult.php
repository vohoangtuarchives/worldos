<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\EvolutionConstants;

final class SimulationResult
{
    /** @param list<array{tick: int, state: array<string, float>, arc_phase: string}> $snapshots */
    /** @param list<array{tick: int, event: string}> $events */
    /** @param array{tick: list<int>, entropy: list<float>, arc_phase: list<string>} $metrics */
    public function __construct(
        public readonly array $snapshots,
        public readonly array $events,
        public readonly array $metrics
    ) {
    }
}


