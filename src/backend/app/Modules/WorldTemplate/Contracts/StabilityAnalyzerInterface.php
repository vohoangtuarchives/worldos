<?php

declare(strict_types=1);

namespace App\Modules\WorldTemplate\Contracts;

use App\Modules\Shared\ValueObjects\CascadeStateVector;
use App\Modules\Shared\ValueObjects\StabilityMetric;
use App\Modules\Shared\ValueObjects\WorldStateVector;

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
