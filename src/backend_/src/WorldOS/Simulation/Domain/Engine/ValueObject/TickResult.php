<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Domain\Engine\ValueObject;

/**
 * Immutable output of a single simulation tick.
 * Serves as the handshake between the EvolutionOperator and the Universe aggregate.
 */
final class TickResult
{
    /**
     * @param AnomalyEvent[] $anomalies
     */
    public function __construct(
        public readonly int         $tick,
        public readonly int         $seed,
        public readonly StateVector $nextStateVector,
        public readonly float       $entropyDelta,
        public readonly float       $existenceWeight,
        public readonly array       $anomalies
    ) {
    }

    public function hasAnomalies(): bool
    {
        return count($this->anomalies) > 0;
    }

    public function totalAnomalyScore(): float
    {
        return array_sum(array_map(
            fn(AnomalyEvent $e) => $e->intensity,
            $this->anomalies
        ));
    }

    public function toArray(): array
    {
        return [
            'tick'             => $this->tick,
            'seed'             => $this->seed,
            'entropy_delta'    => $this->entropyDelta,
            'existence_weight' => $this->existenceWeight,
            'anomalies'        => array_map(fn($a) => $a->toArray(), $this->anomalies),
            'state_vector'     => $this->nextStateVector->toArray(),
        ];
    }
}
