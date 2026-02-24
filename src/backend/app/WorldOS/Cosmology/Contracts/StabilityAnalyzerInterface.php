<?php

declare(strict_types=1);

namespace App\WorldOS\Cosmology\Contracts;

use App\WorldOS\Shared\ValueObjects\CascadeStateVector;
use App\WorldOS\Shared\ValueObjects\StabilityMetric;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * Stability Analyzer Contract.
 *
 * Computes σ(U) stability metric from state vectors.
 * Implementation lives in SimulationEngine bounded context.
 */
interface StabilityAnalyzerInterface
{
    public function analyze(
        WorldStateVector $state,
        CascadeStateVector $cascade,
    ): StabilityMetric;
}
