<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Evolution;

use App\Domains\Cosmology\Entities\WorldStateVector;

/**
 * Multipliers applied to preset parameters by arc phase. Effective = preset * modifier.
 * paramMultipliers keyed by BasePhysicsEngine param name (e.g. entropy_inequality_rate).
 */
final class RegimeModifier
{
    /** @param array<string, float> $paramMultipliers param name => multiplier */
    public function __construct(
        public readonly array $paramMultipliers = [],
        public readonly array $mutationMultiplier = [],
        public readonly float $instabilityMultiplier = 1.0
    ) {
    }

    public function multiplierFor(string $paramName): float
    {
        return $this->paramMultipliers[$paramName] ?? 1.0;
    }

    public static function forPhase(ArcPhase $phase): self
    {
        return match ($phase) {
            ArcPhase::GOLDEN_AGE => new self(
                paramMultipliers: [
                    'resource_innovation_yield' => 1.3,
                    'order_cohesion_rate' => 1.2,
                    'entropy_dampening' => 1.2,
                ],
                mutationMultiplier: ['renaissance' => 1.5, 'collapse' => 0.6],
                instabilityMultiplier: 0.7
            ),
            ArcPhase::CRISIS => new self(
                paramMultipliers: [
                    'entropy_inequality_rate' => 1.4,
                    'entropy_stagnation_rate' => 1.4,
                    'order_cohesion_rate' => 0.7,
                ],
                mutationMultiplier: ['civil_war' => 1.8, 'collapse' => 1.5],
                instabilityMultiplier: 1.6
            ),
            ArcPhase::COLLAPSE => new self(
                paramMultipliers: [
                    'entropy_inequality_rate' => 1.8,
                    'entropy_trauma_rate' => 1.5,
                    'order_entropy_decay' => 0.5,
                ],
                mutationMultiplier: ['collapse' => 2.0, 'fragmentation' => 1.7],
                instabilityMultiplier: 2.2
            ),
            default => new self(),
        };
    }
}
