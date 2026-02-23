<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\StateVector;

/**
 * ChaosDynamics
 *
 * Defines the highly variable, high-entropy fallback forces that occur
 * when a civilization loses structural coherence but maintains energy.
 * This is not a "collapse", but a fragmented, volatile state.
 */
class ChaosDynamics
{
    public function apply(array $v, float $gain = 1.0, float $dt = 1.0): array
    {
        $d = array_fill(0, StateVector::DIMENSIONS, 0.0);
        $keys = StateVector::KEYS;

        $idxTech   = array_search('tech', $keys);
        $idxLegit  = array_search('legitimacy', $keys);
        $idxStab   = array_search('stability', $keys);
        $idxMp     = array_search('mp', $keys);
        $idxIe     = array_search('ie', $keys);
        $idxIneq   = array_search('inequality', $keys);
        $idxElite  = array_search('eliteCohesion', $keys);
        
        // --- CHAOS VOLATILITY ---

        // 1. Central Legitimacy and Stability crash rapidly
        $d[$idxLegit]  -= $v[$idxIe] * 0.15 * $gain;
        $d[$idxStab]   -= $v[$idxIneq] * $v[$idxIe] * 0.2 * $gain;

        // 2. Military Pressure and Inequality spike (Warlord era)
        $d[$idxMp]     += $v[$idxIe] * 0.15 * $gain;
        $d[$idxIneq]   += $v[$idxMp] * 0.1 * $gain;

        // 3. Elite Cohesion shatters
        $d[$idxElite]  -= $v[$idxIe] * 0.1 * $gain;

        // 4. Technology becomes a random walk (high variance, some loss, some spike)
        // Chaos can spark desperate innovation or destroy knowledge
        $techVariance = (mt_rand() / mt_getrandmax() * 0.2) - 0.1; // -0.1 to +0.1
        $d[$idxTech]   += $techVariance * $v[$idxIe] * $gain;

        return $d;
    }
}
