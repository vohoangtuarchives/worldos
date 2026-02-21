<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Mathematics;

use Tuzy\Domain\Evolution\ValueObject\StateVector;

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

        // tech * stability -> prosperity grows
        $d[array_search('prosperity', $keys)] += 0.02 * $v[array_search('tech', $keys)] * $v[array_search('stability', $keys)];

        // entropy * inequality -> rapidly crashes stability
        $d[array_search('stability', $keys)] -= 0.03 * $v[array_search('ie', $keys)] * $v[array_search('inequality', $keys)];

        // tech * info -> raises volatility proxy (curvature)
        $d[array_search('curvature', $keys)] += 0.01 * $v[array_search('tech', $keys)] * $v[array_search('info', $keys)];

        // prosperity * expansionism -> resource depletion (sustainability drops)
        $d[array_search('sustainability', $keys)] -= 0.03 * $v[array_search('prosperity', $keys)] * $v[array_search('expansion', $keys)];

        // sc * mystery -> cultural energy spike
        $d[array_search('ce', $keys)] += 0.02 * $v[array_search('sc', $keys)] * $v[array_search('mystery', $keys)];

        return $d;
    }
}
