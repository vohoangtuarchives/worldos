<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\CivilizationSnapshot;
use Tuzy\Domain\Evolution\ValueObject\CosmicState;

/**
 * DynamicsAnalyzer
 * 
 * Analyzes the trajectory of the world state in phase space.
 * Uses math from WorldStateVector Legacy: Curvature, Divergence.
 */
class DynamicsAnalyzer
{
    /**
     * Curvature: Magnitude of the gradient (rate of change) from prev to current.
     * High curvature = trajectory changing fast = instability stress.
     */
    public function calculateCurvature(
        CivilizationSnapshot $current, 
        CivilizationSnapshot $prev
    ): float {
        $dimensions = [
            'culturalEnergy', 'spiritualCohesion', 'technologicalLevel', 
            'stability', 'prosperity', 'militaryPressure', 
            'legitimacy', 'eliteCohesion', 'inequality'
        ];

        $sumSquares = 0.0;
        foreach ($dimensions as $dim) {
            $delta = $current->$dim - $prev->$dim;
            $sumSquares += $delta * $delta;
        }

        return sqrt($sumSquares);
    }

    /**
     * Divergence: Scalar measure of "spread" or instability.
     * Defined as variance of components.
     */
    public function calculateDivergence(CivilizationSnapshot $state): float
    {
        $vals = [
            $state->culturalEnergy, $state->spiritualCohesion, 
            $state->technologicalLevel / 2.0, // Norm to 1.0
            $state->stability, $state->prosperity, 
            $state->militaryPressure, $state->legitimacy, 
            $state->eliteCohesion, $state->inequality
        ];

        $n = count($vals);
        $mean = array_sum($vals) / $n;
        
        $variance = 0.0;
        foreach ($vals as $v) {
            $variance += ($v - $mean) ** 2;
        }

        return $variance / $n;
    }

    /**
     * Calculate Bifurcation Probability.
     */
    public function calculateBranchProbability(
        float $curvature, 
        float $divergence, 
        float $pressure
    ): float {
        // High curvature + high pressure -> higher probability
        $chaosIndex = ($curvature * 2.0) + ($divergence * 0.5) + ($pressure * 1.5);
        
        // Sigmoid mapping
        return 1.0 / (1.0 + exp(-10.0 * ($chaosIndex - 0.5)));
    }
}
