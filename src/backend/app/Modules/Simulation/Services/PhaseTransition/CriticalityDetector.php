<?php

declare(strict_types=1);

namespace App\Modules\Simulation\Services\PhaseTransition;

use App\Modules\Shared\ValueObjects\WorldStateVector;

/**
 * Criticality Detector — determines civilizational phase state.
 *
 * From docs §18.2:
 *   STABLE → REORGANIZATION_POSSIBLE → CRITICAL → COLLAPSE_IMMINENT
 *   Collapse when: contradiction > 0.70, innovation < 0.15, resource_flow < 0.05
 *
 * Pure computation — NO side effects, NO Laravel dependencies.
 */
final class CriticalityDetector
{
    /**
     * Criticality level constants.
     */
    public const STABLE = 'stable';
    public const REORGANIZATION_POSSIBLE = 'reorganization_possible';
    public const CRITICAL = 'critical';
    public const COLLAPSE_IMMINENT = 'collapse_imminent';

    /**
     * Determine the criticality level from state + pressure.
     *
     * @return string One of STABLE, REORGANIZATION_POSSIBLE, CRITICAL, COLLAPSE_IMMINENT
     */
    public function detect(WorldStateVector $state, PressureField $pressure): string
    {
        $contradiction = $pressure->calculateContradictionIndex($state);
        $accumulatedPressure = $pressure->getAccumulatedPressure();

        // COLLAPSE_IMMINENT: extreme conditions from docs §18.2
        if (
            $contradiction > 0.70
            && $state->innovation < 0.15
            && $this->estimateResourceFlow($state) < 0.05
        ) {
            return self::COLLAPSE_IMMINENT;
        }

        // CRITICAL: high pressure, high contradiction, deteriorating
        if ($accumulatedPressure > 0.7 && $contradiction > 0.55) {
            return self::CRITICAL;
        }

        // REORGANIZATION_POSSIBLE: entropy high enough for reorganization
        // From docs: entropy > 0.65 → innovation burst possible
        if ($accumulatedPressure > 0.4 || ($state->entropy > 0.65 && $contradiction > 0.35)) {
            return self::REORGANIZATION_POSSIBLE;
        }

        return self::STABLE;
    }

    /**
     * Check if the system can reorganize (entropy → innovation burst).
     * From docs §18.2: can_reorganize → InnovationBurst
     */
    public function canReorganize(WorldStateVector $state, PressureField $pressure): bool
    {
        $level = $this->detect($state, $pressure);

        return $level === self::REORGANIZATION_POSSIBLE
            && $state->entropy > 0.65
            && $state->cohesion > 0.2; // Need some cohesion to reorganize
    }

    /**
     * Check if collapse is deterministic (no escape possible).
     */
    public function isCollapseInevitable(WorldStateVector $state, PressureField $pressure): bool
    {
        $level = $this->detect($state, $pressure);

        if ($level !== self::COLLAPSE_IMMINENT) {
            return false;
        }

        // Collapse inevitable if also cohesion broken and order failing
        return $state->cohesion < 0.1 && $state->order < 0.2;
    }

    /**
     * Estimate resource flow from state vector.
     * resource_flow ≈ f(innovation, order, legitimacy)
     */
    private function estimateResourceFlow(WorldStateVector $state): float
    {
        return ($state->innovation * 0.4 + $state->order * 0.3 + $state->legitimacy * 0.3);
    }
}
