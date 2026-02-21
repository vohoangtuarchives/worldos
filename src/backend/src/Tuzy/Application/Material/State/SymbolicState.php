<?php

namespace Tuzy\Application\Material\State;

/**
 * SymbolicState - Belief and Identity Metrics
 * 
 * Scale: 0.0 - 1.0
 */
class SymbolicState
{
    public function __construct(
        public readonly float $mythStrength,      // Founding myth potency
        public readonly float $beliefExtremism,   // Religious/ideological intensity
        public readonly float $legitimacy,        // Regime legitimacy
        public readonly float $ritualization,     // Ritual practice intensity
        public readonly float $identityRigidity   // Identity flexibility
    ) {}

    public static function createNeutral(): self
    {
        return new self(
            mythStrength: 0.6,
            beliefExtremism: 0.3,
            legitimacy: 0.5,
            ritualization: 0.4,
            identityRigidity: 0.4
        );
    }

    public function toArray(): array
    {
        return [
            'myth_strength' => $this->mythStrength,
            'belief_extremism' => $this->beliefExtremism,
            'legitimacy' => $this->legitimacy,
            'ritualization' => $this->ritualization,
            'identity_rigidity' => $this->identityRigidity,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            mythStrength: $data['myth_strength'],
            beliefExtremism: $data['belief_extremism'],
            legitimacy: $data['legitimacy'],
            ritualization: $data['ritualization'],
            identityRigidity: $data['identity_rigidity']
        );
    }

    public function applyDelta(array $deltas): self
    {
        return new self(
            mythStrength: $this->clamp($this->mythStrength + ($deltas['myth_strength'] ?? 0)),
            beliefExtremism: $this->clamp($this->beliefExtremism + ($deltas['belief_extremism'] ?? 0)),
            legitimacy: $this->clamp($this->legitimacy + ($deltas['legitimacy'] ?? 0)),
            ritualization: $this->clamp($this->ritualization + ($deltas['ritualization'] ?? 0)),
            identityRigidity: $this->clamp($this->identityRigidity + ($deltas['identity_rigidity'] ?? 0))
        );
    }

    private function clamp(float $value): float
    {
        return min(1.0, max(0.0, $value));
    }
}
