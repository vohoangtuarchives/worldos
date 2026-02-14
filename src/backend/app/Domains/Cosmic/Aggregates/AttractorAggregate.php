<?php

declare(strict_types=1);

namespace App\Domains\Cosmic\Aggregates;

use App\Domains\Cosmic\ValueObjects\AttractorIncarnation;

/**
 * AttractorAggregate is the root entity representing an attractor across time.
 * It maintains a tree of incarnations (evolutionary history).
 */
class AttractorAggregate
{
    private array $incarnations = [];

    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public string $lifecycleState = 'EMERGENT', // EMERGENT|DOMINANT|DECLINING|EXTINCT
        public array $historicalInertia = [],
        public float $cumulativeRebirthGain = 0.0,
        public float $identityKarmaIndex = 0.0,
        public string $phaseState = 'STABLE', // STABLE|CHAOTIC_TRANSITION|RECONSOLIDATING
        private ?string $currentIncarnationId = null
    ) {}

    public function addIncarnation(AttractorIncarnation $incarnation): void
    {
        $this->incarnations[$incarnation->id] = $incarnation;
        $this->currentIncarnationId = $incarnation->id;
    }

    public function getCurrentIncarnation(): ?AttractorIncarnation
    {
        if ($this->currentIncarnationId === null) {
            return null;
        }

        return $this->incarnations[$this->currentIncarnationId] ?? null;
    }

    public function getIncarnationTree(): array
    {
        return $this->incarnations;
    }

    public function setCurrentIncarnationId(?string $id): void
    {
        $this->currentIncarnationId = $id;
    }

    public function getCurrentIncarnationId(): ?string
    {
        return $this->currentIncarnationId;
    }

    public function loadIncarnations(array $incarnations): void
    {
        foreach ($incarnations as $inc) {
            if ($inc instanceof AttractorIncarnation) {
                $this->incarnations[$inc->id] = $inc;
            }
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'lifecycle_state' => $this->lifecycleState,
            'historical_inertia' => $this->historicalInertia,
            'cumulative_rebirth_gain' => $this->cumulativeRebirthGain,
            'identity_karma_index' => $this->identityKarmaIndex,
            'phase_state' => $this->phaseState,
            'current_incarnation_id' => $this->currentIncarnationId,
        ];
    }
}
