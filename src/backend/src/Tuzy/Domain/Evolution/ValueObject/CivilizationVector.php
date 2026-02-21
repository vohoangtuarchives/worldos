<?php

namespace Tuzy\Domain\Evolution\ValueObject;

class CivilizationVector
{
    public function __construct(
        public readonly float $innovationRate,
        public readonly float $innovationResistance,
        public readonly float $hierarchyTendency,
        public readonly float $conflictDrive,
        public readonly float $cooperationBias,
        public readonly float $resourceDistributionSkew,
        public readonly float $populationGrowthPressure
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['innovation_rate'],
            $data['innovation_resistance'],
            $data['hierarchy_tendency'],
            $data['conflict_drive'],
            $data['cooperation_bias'],
            $data['resource_distribution_skew'],
            $data['population_growth_pressure']
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


