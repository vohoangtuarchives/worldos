<?php

declare(strict_types=1);

namespace App\Domains\Saga\Services;

/**
 * Phase 4.1: Hard constraint for Pareto — if violated, outcome does not enter Pareto front.
 * Doc: resilience < 0.45 || entropyControl < 0.4 → violated.
 */
final class StabilityConstraint
{
    private const RESILIENCE_MIN = 0.45;
    private const ENTROPY_CONTROL_MIN = 0.4;

    /**
     * Whether the objective vector violates stability.
     *
     * @param array<string, float> $objectiveVector Keys e.g. resilience, entropy_control
     */
    public function violated(array $objectiveVector): bool
    {
        $resilience = (float) ($objectiveVector['resilience'] ?? $objectiveVector['resilienceIndex'] ?? 0.5);
        $entropyControl = (float) ($objectiveVector['entropy_control'] ?? 0.5);
        return $resilience < self::RESILIENCE_MIN || $entropyControl < self::ENTROPY_CONTROL_MIN;
    }
}
