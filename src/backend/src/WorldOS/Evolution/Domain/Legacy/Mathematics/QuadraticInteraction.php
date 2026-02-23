<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Mathematics;

use WorldOS\Evolution\Domain\Legacy\ValueObject\StateVector;

/**
 * QuadraticInteraction Q(S)
 * 
 * Defines highly targeted non-linear combinations between dimensions.
 * Avoiding full N^3 tensors, we just pick meaningful cross-products.
 */
class QuadraticInteraction
{
    public function apply(StateVector $S): array
    {
        $d = array_fill(0, StateVector::DIMENSIONS, 0.0);
        $v = $S->values;
        $keys = StateVector::KEYS;

        $idxTech = array_search('tech', $keys);
        $idxProsperity = array_search('prosperity', $keys);
        $idxStab = array_search('stability', $keys);
        $idxIe = array_search('ie', $keys);
        $idxIneq = array_search('inequality', $keys);
        $idxSc = array_search('sc', $keys);
        $idxMystery = array_search('mystery', $keys);
        $idxCe = array_search('ce', $keys);
        $idxExp = array_search('expansion', $keys);
        $idxSus = array_search('sustainability', $keys);
        
        // 1. Superlinear Growth: Tech and Prosperity mutually amplify!
        // tech_growth = alpha * tech * economy - beta * entropy * tech
        $d[$idxTech] += 0.06 * $v[$idxTech] * $v[$idxProsperity] - 0.03 * $v[$idxIe] * $v[$idxTech];
        
        // prosperity_growth = alpha * tech * prosperity - beta * entropy * prosperity
        $d[$idxProsperity] += 0.06 * $v[$idxTech] * $v[$idxProsperity] - 0.04 * $v[$idxIe] * $v[$idxProsperity];
        
        // tech * stability -> prosperity grows (amplified)
        $d[$idxProsperity] += 0.04 * $v[$idxTech] * $v[$idxStab]; // Was 0.02

        // 2. Instability Amplifier & Cascade Shock
        // entropy * inequality -> rapidly crashes stability AND amplifies entropy itself
        $d[$idxStab] -= 0.06 * $v[$idxIe] * $v[$idxIneq]; // Was 0.03
        $d[$idxIe] += 0.04 * $v[$idxIe] * $v[$idxIneq]; // Multiplicative strain growth

        // prosperity * expansionism -> resource depletion (sustainability drops)
        $d[$idxSus] -= 0.05 * $v[$idxProsperity] * $v[$idxExp]; // Was 0.03

        // sc * mystery -> cultural energy spike
        $d[$idxCe] += 0.04 * $v[$idxSc] * $v[$idxMystery]; // Was 0.02

        return $d;
    }
}
