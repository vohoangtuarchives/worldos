<?php

declare(strict_types=1);

namespace App\WorldOS\Cosmology\Contracts;

use App\WorldOS\Cosmology\ValueObjects\CascadeThresholds;
use App\WorldOS\Shared\ValueObjects\CascadeStateVector;
use App\WorldOS\Shared\ValueObjects\LawVector;

/**
 * Cascade Engine Contract.
 *
 * Evolves CascadeStateVector (P→C→B→N→K) by one tick.
 * Implementation lives in SimulationEngine bounded context.
 */
interface CascadeEngineInterface
{
    public function evolve(
        CascadeStateVector $state,
        LawVector $law,
        CascadeThresholds $thresholds,
    ): CascadeStateVector;
}
