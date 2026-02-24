<?php

declare(strict_types=1);

namespace App\WorldOS\SimulationEngine\PhaseTransition;

use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * Pressure Accumulation Field — tracks civilizational pressure.
 *
 * From docs §18.2:
 *   contradiction_index = inequality × (1 - legitimacy) + trauma_factor + entropy_excess
 *   pressure() accumulates; releaseRate() = innovation dissipation
 *
 * Pure computation — NO side effects, NO Laravel dependencies.
 */
final class PressureField
{
    private float $accumulatedPressure;

    public function __construct(
        float $initialPressure = 0.0,
    ) {
        $this->accumulatedPressure = $initialPressure;
    }

    /**
     * Calculate the contradiction index from current state.
     *
     * contradiction = inequality × (1 - legitimacy) + trauma_factor + entropy_excess
     */
    public function calculateContradictionIndex(WorldStateVector $state): float
    {
        $inequalityPressure = $state->inequality * (1.0 - $state->legitimacy);
        $traumaFactor = $state->trauma * 0.5;
        $entropyExcess = max(0.0, $state->entropy - 0.5) * 0.8;

        $contradiction = $inequalityPressure + $traumaFactor + $entropyExcess;

        return min(1.0, max(0.0, $contradiction));
    }

    /**
     * Calculate the pressure release rate from innovation.
     * Higher innovation = more pressure dissipation.
     */
    public function calculateReleaseRate(WorldStateVector $state): float
    {
        return $state->innovation * 0.3;
    }

    /**
     * Update accumulated pressure based on current state.
     * Pressure builds from contradiction, dissipates from innovation.
     */
    public function accumulate(WorldStateVector $state): float
    {
        $contradiction = $this->calculateContradictionIndex($state);
        $release = $this->calculateReleaseRate($state);

        $delta = $contradiction - $release;
        $this->accumulatedPressure = min(1.0, max(0.0, $this->accumulatedPressure + $delta * 0.1));

        return $this->accumulatedPressure;
    }

    public function getAccumulatedPressure(): float
    {
        return $this->accumulatedPressure;
    }

    /**
     * Reset pressure (e.g., after collapse/reorganization).
     */
    public function reset(): void
    {
        $this->accumulatedPressure = 0.0;
    }
}
