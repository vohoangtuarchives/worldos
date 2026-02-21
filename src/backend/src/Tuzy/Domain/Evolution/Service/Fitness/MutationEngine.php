<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service\Fitness;

use Tuzy\Domain\Evolution\ValueObject\StrategyVector;
use Tuzy\Domain\Evolution\Mathematics\LinearCouplingMatrix;

class MutationEngine
{
    /**
     * Mutates a strategy vector by adding noise and re-normalizing.
     */
    public function mutateStrategy(StrategyVector $strategy, float $rate): StrategyVector
    {
        $weights = $strategy->weights;
        foreach ($weights as $key => $val) {
            // Apply drift: +/- rate
            $drift = (mt_rand() / mt_getrandmax() * 2.0 - 1.0) * $rate;
            $weights[$key] = max(0.01, $val + $drift);
        }

        return new StrategyVector($weights);
    }

    /**
     * Mutates the coupling matrix weights.
     * Caution: This can affect global stability.
     */
    public function mutateCoupling(LinearCouplingMatrix $matrix, float $rate): LinearCouplingMatrix
    {
        $A = $matrix->A;
        $n = count($A);

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                // Only mutate non-zero couplings or small chance to spawn new ones
                if ($A[$i][$j] !== 0.0 || (mt_rand() / mt_getrandmax() < 0.05)) {
                    $drift = (mt_rand() / mt_getrandmax() * 2.0 - 1.0) * $rate * 0.1; // Matrix mutation is more sensitive
                    $A[$i][$j] += $drift;
                    
                    // Keep values in a sane range to prevent immediate explosion
                    $A[$i][$j] = max(-0.5, min(0.5, $A[$i][$j]));
                }
            }
        }

        return new LinearCouplingMatrix($A);
    }

    /**
     * Higher stress increases mutation rate (directed mutation).
     */
    public function calculateAdaptiveRate(float $baseRate, float $stress): float
    {
        // rate = base * (1 + alpha * stress)
        $alpha = 2.0;
        return $baseRate * (1.0 + $alpha * $stress);
    }
}
