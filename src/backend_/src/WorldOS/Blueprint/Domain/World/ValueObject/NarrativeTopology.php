<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\World\ValueObject;

use InvalidArgumentException;

/**
 * Value Object representing how the deterministic state is interpreted into a narrative.
 */
final class NarrativeTopology
{
    /**
     * @param float $conflictDensity Base probability of conflict occurrence (0.0 to 1.0)
     * @param float $protagonistWindow Size of the window allowing individuals to temporarily exceed soft caps (0.0 to 1.0)
     * @param float $powerCeilingElasticity How much the narrative allows stretching the physics caps (0.0 to 1.0)
     * @param array $factionEmergenceBiases Bias parameters for multi-polar or uni-polar faction creation
     */
    public function __construct(
        private readonly float $conflictDensity = 0.5,
        private readonly float $protagonistWindow = 0.1,
        private readonly float $powerCeilingElasticity = 0.2,
        private readonly array $factionEmergenceBiases = []
    ) {
        $this->validateFloatRange($this->conflictDensity, 'Conflict density');
        $this->validateFloatRange($this->protagonistWindow, 'Protagonist window');
        $this->validateFloatRange($this->powerCeilingElasticity, 'Power ceiling elasticity');
    }

    public static function create(
        float $conflictDensity = 0.5,
        float $protagonistWindow = 0.1,
        float $powerCeilingElasticity = 0.2,
        array $factionEmergenceBiases = []
    ): self {
        return new self($conflictDensity, $protagonistWindow, $powerCeilingElasticity, $factionEmergenceBiases);
    }

    private function validateFloatRange(float $value, string $name): void
    {
        if ($value < 0.0 || $value > 1.0) {
            throw new InvalidArgumentException("{$name} must be between 0.0 and 1.0.");
        }
    }

    public function getConflictDensity(): float
    {
        return $this->conflictDensity;
    }

    public function getProtagonistWindow(): float
    {
        return $this->protagonistWindow;
    }

    public function getPowerCeilingElasticity(): float
    {
        return $this->powerCeilingElasticity;
    }

    public function getFactionEmergenceBiases(): array
    {
        return $this->factionEmergenceBiases;
    }

    public function toArray(): array
    {
        return [
            'conflict_density' => $this->conflictDensity,
            'protagonist_window' => $this->protagonistWindow,
            'power_ceiling_elasticity' => $this->powerCeilingElasticity,
            'faction_emergence_biases' => $this->factionEmergenceBiases,
        ];
    }
}
