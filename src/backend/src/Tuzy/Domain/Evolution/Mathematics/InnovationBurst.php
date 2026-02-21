<?php

namespace Tuzy\Domain\Evolution\Mathematics;

use Tuzy\Domain\Evolution\ValueObject\WorldStateVector;
use Tuzy\Domain\Evolution\ValueObject\StateVector;

/**
 * Non-linear Innovation Burst & Multiplicative Noise
 *
 * Implements multiplicative divergence and stochastic shocks.
 */
class InnovationBurst
{
    protected float $burstAmplitude = 0.25;

    /**
     * Applies multiplicative noise (divergence) and potential innovation spikes.
     * X += X * random(-eps, eps)
     */
    public function apply(array $values, float $baseVolatility = 0.5): array
    {
        $n = count($values);
        $d = array_fill(0, $n, 0.0);
        
        // High mutation rate (multiplicative noise) to break attractor basins
        // The user requested x3 to x5 higher mutation rate to increase seed divergence
        $mutationFactor = 0.15 * $baseVolatility; 

        for ($i = 0; $i < $n; $i++) {
            // Noise is between -1 and 1
            $noise = (mt_rand() / mt_getrandmax() - 0.5) * 2.0;
            // The change is proportional to the current value (Multiplicative Shock)
            // If the dimension is saturated, the shock causes it to drift back.
            $d[$i] = $values[$i] * $noise * $mutationFactor;
        }

        // Provide a special chaotic spike to technology when entropy is high
        $keys = StateVector::KEYS;
        $idxTech = array_search('tech', $keys);
        $idxIe = array_search('ie', $keys);

        if ($idxTech !== false && $idxIe !== false) {
            $entropy = $values[$idxIe];
            if ($entropy > 0.65) {
                // High entropy forces extreme technological or structural mutation
                $burstRoll = mt_rand() / mt_getrandmax();
                if ($burstRoll < 0.28) {
                    $d[$idxTech] += $this->burstAmplitude * $entropy;
                }
            }
        }

        return $d;
    }
}
