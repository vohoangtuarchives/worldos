<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\StateVector;

/**
 * EmpireDynamics
 *
 * Defines the massive, superlinear multiplicative feedback loops
 * that characterize a Golden Age or Hyper-Empire attractor state.
 */
class EmpireDynamics
{
    public function apply(array $v, float $gain = 1.0, float $dt = 1.0): array
    {
        $d = array_fill(0, StateVector::DIMENSIONS, 0.0);
        $keys = StateVector::KEYS;

        $idxTech   = array_search('tech', $keys);
        $idxProsp  = array_search('prosperity', $keys);
        $idxLegit  = array_search('legitimacy', $keys);
        $idxStab   = array_search('stability', $keys);
        $idxCe     = array_search('ce', $keys);
        $idxMp     = array_search('mp', $keys);
        $idxExp    = array_search('expansion', $keys);

        // --- SUPERLINEAR GROWTH (THE EMPIRE FEEDBACK) ---
        // 1. Tech & Prosperity amplify each other geometrically when Legitimacy is high
        $d[$idxTech]   += ($v[$idxTech] * 0.15 + $v[$idxProsp] * 0.1) * $v[$idxLegit] * $gain;
        $d[$idxProsp]  += ($v[$idxTech] * 0.1 + $v[$idxProsp] * 0.15) * $v[$idxStab] * $gain;

        // 2. Cultural Energy explodes during a golden age
        $d[$idxCe]     += $v[$idxProsp] * $v[$idxLegit] * 0.2 * $gain;

        // 3. Imperial Expansion Loop
        // Prosperity funds military, military funds expansionism
        $d[$idxMp]     += $v[$idxProsp] * 0.1 * $gain;
        $d[$idxExp]    += $v[$idxMp] * $v[$idxStab] * 0.2 * $gain;

        // 4. Entropy Suppression (Empire crushes dissent efficiently)
        $idxIe = array_search('ie', $keys);
        $d[$idxIe]     -= $v[$idxLegit] * $v[$idxStab] * 0.1 * $gain;

        return $d;
    }
}
