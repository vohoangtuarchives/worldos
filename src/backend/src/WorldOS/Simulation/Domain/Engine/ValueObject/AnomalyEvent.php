<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Domain\Engine\ValueObject;

/**
 * Represents a single anomaly detected in the simulation state.
 */
final class AnomalyEvent
{
    public function __construct(
        public readonly string $dimension,
        public readonly float  $value,
        public readonly float  $threshold,
        public readonly float  $intensity // normalized 0.0–1.0
    ) {
    }

    public function toArray(): array
    {
        return [
            'dimension' => $this->dimension,
            'value'     => $this->value,
            'threshold' => $this->threshold,
            'intensity' => $this->intensity,
        ];
    }
}
