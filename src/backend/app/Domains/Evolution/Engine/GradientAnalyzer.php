<?php

declare(strict_types=1);

namespace App\Domains\Evolution\Engine;

use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Mathematics\Vector;

/**
 * GradientAnalyzer - Computes gradient (state - prev) and its magnitude.
 */
final class GradientAnalyzer
{
    public function gradient(WorldStateVector $state, WorldStateVector $prev): Vector
    {
        return $state->gradient($prev);
    }

    public function gradientMagnitude(WorldStateVector $state, WorldStateVector $prev): float
    {
        return $state->curvature($prev);
    }
}
