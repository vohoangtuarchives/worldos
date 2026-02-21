<?php

namespace Tuzy\Application\Material\State;

/**
 * CoreState - Physical Survival Metrics
 * 
 * Scale: 0.0 - 1.0
 */
class CoreState
{
    public function __construct(
        public readonly float $population,        // 0=extinct, 1=thriving
        public readonly float $subsistenceBase,   // Food/resource security
        public readonly float $resourcePressure,  // Resource scarcity
        public readonly float $mortalityRate,     // Death rate
        public readonly float $healthIndex        // General health
    ) {}

    public static function createNeutral(): self
    {
        return new self(
            population: 0.5,
            subsistenceBase: 0.5,
            resourcePressure: 0.5,
            mortalityRate: 0.3,
            healthIndex: 0.5
        );
    }

    public function toArray(): array
    {
        return [
            'population' => $this->population,
            'subsistence_base' => $this->subsistenceBase,
            'resource_pressure' => $this->resourcePressure,
            'mortality_rate' => $this->mortalityRate,
            'health_index' => $this->healthIndex,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            population: $data['population'],
            subsistenceBase: $data['subsistence_base'],
            resourcePressure: $data['resource_pressure'],
            mortalityRate: $data['mortality_rate'],
            healthIndex: $data['health_index']
        );
    }

    public function applyDelta(array $deltas): self
    {
        return new self(
            population: $this->clamp($this->population + ($deltas['population'] ?? 0)),
            subsistenceBase: $this->clamp($this->subsistenceBase + ($deltas['subsistence_base'] ?? 0)),
            resourcePressure: $this->clamp($this->resourcePressure + ($deltas['resource_pressure'] ?? 0)),
            mortalityRate: $this->clamp($this->mortalityRate + ($deltas['mortality_rate'] ?? 0)),
            healthIndex: $this->clamp($this->healthIndex + ($deltas['health_index'] ?? 0))
        );
    }

    private function clamp(float $value): float
    {
        return min(1.0, max(0.0, $value));
    }
}
