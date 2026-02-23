<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\StrategyVector;

/**
 * Applies evolutionary game theory using Replicator Dynamics.
 * Strategies with higher fitness reproduce and spread faster.
 */
class ReplicatorDynamics
{
    /**
     * Updates the strategy vector based on the fitness of each doctrine.
     * x'_i = x_i * (f_i - f_avg) * dt
     * 
     * @param StrategyVector $currentStrategy
     * @param array<string, float> $fitnessProfile The fitness [0, \infty) of each strategy type
     * @param float $mutationRate (Evolution Rate \eta)
     * @param float $dt
     */
    public function evolve(
        StrategyVector $currentStrategy, 
        array $fitnessProfile, 
        float $mutationRate,
        float $dt = 1.0
    ): StrategyVector {
        $averageFitness = 0.0;
        foreach (StrategyVector::DIMENSIONS as $dim) {
            $weight = $currentStrategy->weights[$dim];
            $fitness = $fitnessProfile[$dim] ?? 1.0;
            $averageFitness += $weight * $fitness;
        }

        if ($averageFitness <= 0) {
            return $currentStrategy; // Degenerate case fallback
        }

        $nextWeights = [];
        foreach (StrategyVector::DIMENSIONS as $dim) {
            $weight = $currentStrategy->weights[$dim];
            $fitness = $fitnessProfile[$dim] ?? 1.0;
            
            // Replicator Equation
            $derivative = $weight * ($fitness - $averageFitness);

            // Apply standard reproduction + add mutation pressure (random drift)
            // Mutation noise relies on the mutationRate parameter
            $noise = (mt_rand() / mt_getrandmax() - 0.5) * $mutationRate;
            
            // X(t+1) = X(t) + dt * dX/dt + Noise
            $nextWeights[$dim] = max(0.001, $weight + $dt * $derivative + $noise);
        }

        // Pass back into StrategyVector to normalize to 1.0
        return new StrategyVector($nextWeights);
    }
}
