<?php

namespace Tuzy\Application\Material\State;

/**
 * MemoryState - Historical Consciousness Metrics
 * 
 * Scale: 0.0 - 1.0
 */
class MemoryState
{
    public function __construct(
        public readonly float $traumaDensity,          // Collective trauma load
        public readonly float $nostalgiaPressure,      // Longing for past
        public readonly float $grievanceIndex,         // Historical resentment
        public readonly float $historicalDistortion,   // Memory accuracy (inverse)
        public readonly float $legacyLoad              // Weight of past
    ) {}

    public static function createNeutral(): self
    {
        return new self(
            traumaDensity: 0.3,
            nostalgiaPressure: 0.3,
            grievanceIndex: 0.3,
            historicalDistortion: 0.2,
            legacyLoad: 0.4
        );
    }

    public function toArray(): array
    {
        return [
            'trauma_density' => $this->traumaDensity,
            'nostalgia_pressure' => $this->nostalgiaPressure,
            'grievance_index' => $this->grievanceIndex,
            'historical_distortion' => $this->historicalDistortion,
            'legacy_load' => $this->legacyLoad,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            traumaDensity: $data['trauma_density'],
            nostalgiaPressure: $data['nostalgia_pressure'],
            grievanceIndex: $data['grievance_index'],
            historicalDistortion: $data['historical_distortion'],
            legacyLoad: $data['legacy_load']
        );
    }

    public function applyDelta(array $deltas): self
    {
        return new self(
            traumaDensity: $this->clamp($this->traumaDensity + ($deltas['trauma_density'] ?? 0)),
            nostalgiaPressure: $this->clamp($this->nostalgiaPressure + ($deltas['nostalgia_pressure'] ?? 0)),
            grievanceIndex: $this->clamp($this->grievanceIndex + ($deltas['grievance_index'] ?? 0)),
            historicalDistortion: $this->clamp($this->historicalDistortion + ($deltas['historical_distortion'] ?? 0)),
            legacyLoad: $this->clamp($this->legacyLoad + ($deltas['legacy_load'] ?? 0))
        );
    }

    private function clamp(float $value): float
    {
        return min(1.0, max(0.0, $value));
    }
}
