<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmology\ValueObject;

/**
 * Civilization dimensions for cosmology seed.
 */
readonly class CivilizationVector
{
    public function __construct(
        public float $innovationRate = 0.5,
        public float $innovationResistance = 0.5,
        public float $hierarchyTendency = 0.5,
        public float $conflictDrive = 0.5,
        public float $cooperationBias = 0.5,
        public float $resourceDistributionSkew = 0.5,
        public float $populationGrowthPressure = 0.5,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['innovation_rate'] ?? 0.5),
            (float) ($data['innovation_resistance'] ?? 0.5),
            (float) ($data['hierarchy_tendency'] ?? 0.5),
            (float) ($data['conflict_drive'] ?? 0.5),
            (float) ($data['cooperation_bias'] ?? 0.5),
            (float) ($data['resource_distribution_skew'] ?? 0.5),
            (float) ($data['population_growth_pressure'] ?? 0.5),
        );
    }

    public function toArray(): array
    {
        return [
            'innovation_rate' => $this->innovationRate,
            'innovation_resistance' => $this->innovationResistance,
            'hierarchy_tendency' => $this->hierarchyTendency,
            'conflict_drive' => $this->conflictDrive,
            'cooperation_bias' => $this->cooperationBias,
            'resource_distribution_skew' => $this->resourceDistributionSkew,
            'population_growth_pressure' => $this->populationGrowthPressure,
        ];
    }
}
