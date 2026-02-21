<?php

namespace Tuzy\Application\Material\State;

/**
 * MetaState - System-Level Metrics
 * 
 * NOT directly affected by materials, calculated from other states.
 */
class MetaState
{
    public function __construct(
        public readonly int $epoch,                      // Current tick/year
        public readonly float $entropy,                  // System disorder (0=ordered, 1=chaotic)
        public readonly float $driftRate,                // Spontaneous change rate
        public readonly float $collapseProximity,        // Collapse pressure (not prediction)
        public readonly string $simulationHealth         // 'stable' | 'degrading' | 'critical'
    ) {}

    public static function createInitial(): self
    {
        return new self(
            epoch: 0,
            entropy: 0.3,
            driftRate: 0.1,
            collapseProximity: 0.2,
            simulationHealth: 'stable'
        );
    }

    public function toArray(): array
    {
        return [
            'epoch' => $this->epoch,
            'entropy' => $this->entropy,
            'drift_rate' => $this->driftRate,
            'collapse_proximity' => $this->collapseProximity,
            'simulation_health' => $this->simulationHealth,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            epoch: $data['epoch'],
            entropy: $data['entropy'],
            driftRate: $data['drift_rate'],
            collapseProximity: $data['collapse_proximity'],
            simulationHealth: $data['simulation_health']
        );
    }

    public function withEpoch(int $epoch): self
    {
        return new self(
            epoch: $epoch,
            entropy: $this->entropy,
            driftRate: $this->driftRate,
            collapseProximity: $this->collapseProximity,
            simulationHealth: $this->simulationHealth
        );
    }

    /**
     * Calculate new meta state from other state components.
     */
    public static function calculate(
        int $epoch,
        CoreState $core,
        StructuralState $structural,
        SymbolicState $symbolic,
        MemoryState $memory
    ): self {
        // Entropy: average disorder across states
        $entropy = (
            (1.0 - $core->subsistenceBase) +
            $structural->inequality +
            $symbolic->beliefExtremism +
            $memory->traumaDensity
        ) / 4.0;

        // Drift: rate of spontaneous change
        $driftRate = min(0.5, $entropy * 0.3 + $memory->legacyLoad * 0.2);

        // Collapse proximity: aggregate pressure
        $collapseProximity = (
            (1.0 - $core->subsistenceBase) * 0.3 +
            $structural->inequality * 0.2 +
            (1.0 - $structural->infrastructureIntegrity) * 0.3 +
            $memory->grievanceIndex * 0.2
        );

        // Simulation health
        $simulationHealth = match(true) {
            $collapseProximity > 0.8 => 'critical',
            $collapseProximity > 0.6 => 'degrading',
            default => 'stable',
        };

        return new self(
            epoch: $epoch,
            entropy: min(1.0, max(0.0, $entropy)),
            driftRate: $driftRate,
            collapseProximity: min(1.0, max(0.0, $collapseProximity)),
            simulationHealth: $simulationHealth
        );
    }
}
