<?php

declare(strict_types=1);

namespace App\Domains\Cosmic\ValueObjects;

/**
 * WorldSnapshot - Composite of all 4 evolution layers at a single point in time.
 *
 * This is the complete "state of the world" across all layers.
 * Used for persistence snapshots and narrative rendering.
 */
final class WorldSnapshot
{
    public function __construct(
        public readonly CosmicState $cosmic,
        public readonly EnvironmentState $environment,
        public readonly CivilizationState $civilization,
        public readonly int $year,
    ) {}

    public static function defaultObservation(int $year = 0): self
    {
        return new self(
            cosmic: CosmicState::defaultObservation($year),
            environment: EnvironmentState::defaultObservation($year),
            civilization: CivilizationState::defaultObservation($year),
            year: $year,
        );
    }

    public function toArray(): array
    {
        return [
            'cosmic' => $this->cosmic->toArray(),
            'environment' => $this->environment->toArray(),
            'civilization' => $this->civilization->toArray(),
            'year' => $this->year,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cosmic: CosmicState::fromArray($data['cosmic']),
            environment: EnvironmentState::fromArray($data['environment']),
            civilization: CivilizationState::fromArray($data['civilization']),
            year: (int) $data['year'],
        );
    }

    /**
     * A composite "tension" metric for the narrative renderer.
     * Combines signals from all layers.
     */
    public function compositeTension(): float
    {
        return $this->cosmic->cosmicTension() * 0.4
             + $this->environment->environmentalPressure() * 0.3
             + (1.0 - $this->civilization->factionStability) * 0.3;
    }
}
