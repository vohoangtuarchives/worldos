<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\ValueObject;

use WorldOS\Evolution\Domain\Legacy\Enum\WorldPhase;
use WorldOS\Evolution\Domain\Legacy\ValueObject\LifeState;
use Illuminate\Contracts\Support\Arrayable;

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
        public readonly ?CivilizationSnapshot $civilization, // Nullable
        public readonly WorldField $worldField,
        public readonly WorldPhase $worldPhase, // NEW
        public readonly LifeState $lifeState,   // NEW
        public readonly int $year,
    ) {}

    public static function defaultObservation(int $year = 0): self
    {
        return new self(
            cosmic: CosmicState::defaultObservation($year),
            environment: EnvironmentState::defaultObservation($year),
            civilization: CivilizationSnapshot::defaultObservation($year),
            worldField: WorldField::default(),
            worldPhase: WorldPhase::PRIMORDIAL,
            lifeState: LifeState::primordial(),
            year: $year,
        );
    }

    public function toArray(): array
    {
        return [
            'cosmic' => $this->cosmic->toArray(),
            'environment' => $this->environment->toArray(),
            'civilization' => $this->civilization?->toArray(),
            'world_field' => $this->worldField->toArray(),
            'world_phase' => $this->worldPhase->value,
            'life_state' => $this->lifeState->toArray(),
            'year' => $this->year,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cosmic: CosmicState::fromArray($data['cosmic']),
            environment: EnvironmentState::fromArray($data['environment']),
            civilization: isset($data['civilization']) ? CivilizationSnapshot::fromArray($data['civilization']) : null,
            worldField: isset($data['world_field']) ? WorldField::fromArray($data['world_field']) : WorldField::default(),
            worldPhase: WorldPhase::from($data['world_phase'] ?? WorldPhase::PRIMORDIAL->value),
            lifeState: isset($data['life_state']) ? LifeState::fromArray($data['life_state']) : LifeState::primordial(),
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
             + (1.0 - $this->civilization->stability) * 0.3;
    }
}



