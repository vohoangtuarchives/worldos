<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\EvolutionConstants;

final class SimulationConfig
{
    public function __construct(
        public readonly int $ticks,
        public readonly int $snapshotInterval,
        public readonly int $seed,
        public readonly ?string $universeId = null,
        public readonly ?array $initialState = null
    ) {
    }
}


