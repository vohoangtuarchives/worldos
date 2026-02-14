<?php

namespace App\Domains\Material\State;

/**
 * StructuralState - Social Organization Metrics
 * 
 * Scale: 0.0 - 1.0
 */
class StructuralState
{
    public function __construct(
        public readonly float $inequality,                 // Wealth/power distribution
        public readonly float $laborCoercion,              // Forced labor intensity
        public readonly float $infrastructureIntegrity,    // Physical infrastructure health
        public readonly float $centralization,             // Power concentration
        public readonly float $productivityCeiling,        // Economic output limit
        public readonly float $specializationDepth         // Division of labor
    ) {}

    public static function createNeutral(): self
    {
        return new self(
            inequality: 0.5,
            laborCoercion: 0.3,
            infrastructureIntegrity: 0.5,
            centralization: 0.5,
            productivityCeiling: 0.5,
            specializationDepth: 0.4
        );
    }

    public function toArray(): array
    {
        return [
            'inequality' => $this->inequality,
            'labor_coercion' => $this->laborCoercion,
            'infrastructure_integrity' => $this->infrastructureIntegrity,
            'centralization' => $this->centralization,
            'productivity_ceiling' => $this->productivityCeiling,
            'specialization_depth' => $this->specializationDepth,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            inequality: $data['inequality'],
            laborCoercion: $data['labor_coercion'],
            infrastructureIntegrity: $data['infrastructure_integrity'],
            centralization: $data['centralization'],
            productivityCeiling: $data['productivity_ceiling'],
            specializationDepth: $data['specialization_depth']
        );
    }

    public function applyDelta(array $deltas): self
    {
        return new self(
            inequality: $this->clamp($this->inequality + ($deltas['inequality'] ?? 0)),
            laborCoercion: $this->clamp($this->laborCoercion + ($deltas['labor_coercion'] ?? 0)),
            infrastructureIntegrity: $this->clamp($this->infrastructureIntegrity + ($deltas['infrastructure_integrity'] ?? 0)),
            centralization: $this->clamp($this->centralization + ($deltas['centralization'] ?? 0)),
            productivityCeiling: $this->clamp($this->productivityCeiling + ($deltas['productivity_ceiling'] ?? 0)),
            specializationDepth: $this->clamp($this->specializationDepth + ($deltas['specialization_depth'] ?? 0))
        );
    }

    private function clamp(float $value): float
    {
        return min(1.0, max(0.0, $value));
    }
}
