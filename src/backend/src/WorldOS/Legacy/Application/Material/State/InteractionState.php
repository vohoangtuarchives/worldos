<?php

namespace WorldOS\Legacy\Application\Material\State;

/**
 * InteractionState - External World Relations
 * 
 * Scale: 0.0 - 1.0
 */
class InteractionState
{
    public function __construct(
        public readonly float $externalThreat,     // Military threat level
        public readonly float $migrationPressure,  // Population movement
        public readonly float $tradeExposure,      // Trade network integration
        public readonly float $culturalFriction,   // Cultural conflict
        public readonly float $worldReputation     // International standing
    ) {}

    public static function createNeutral(): self
    {
        return new self(
            externalThreat: 0.3,
            migrationPressure: 0.2,
            tradeExposure: 0.4,
            culturalFriction: 0.3,
            worldReputation: 0.5
        );
    }

    public function toArray(): array
    {
        return [
            'external_threat' => $this->externalThreat,
            'migration_pressure' => $this->migrationPressure,
            'trade_exposure' => $this->tradeExposure,
            'cultural_friction' => $this->culturalFriction,
            'world_reputation' => $this->worldReputation,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            externalThreat: $data['external_threat'],
            migrationPressure: $data['migration_pressure'],
            tradeExposure: $data['trade_exposure'],
            culturalFriction: $data['cultural_friction'],
            worldReputation: $data['world_reputation']
        );
    }

    public function applyDelta(array $deltas): self
    {
        return new self(
            externalThreat: $this->clamp($this->externalThreat + ($deltas['external_threat'] ?? 0)),
            migrationPressure: $this->clamp($this->migrationPressure + ($deltas['migration_pressure'] ?? 0)),
            tradeExposure: $this->clamp($this->tradeExposure + ($deltas['trade_exposure'] ?? 0)),
            culturalFriction: $this->clamp($this->culturalFriction + ($deltas['cultural_friction'] ?? 0)),
            worldReputation: $this->clamp($this->worldReputation + ($deltas['world_reputation'] ?? 0))
        );
    }

    private function clamp(float $value): float
    {
        return min(1.0, max(0.0, $value));
    }
}
