<?php

declare(strict_types=1);

namespace App\Modules\WorldTemplate\Contracts;

use App\Modules\WorldTemplate\ValueObjects\CascadeThresholds;
use App\Modules\Shared\ValueObjects\CascadeStateVector;
use App\Modules\Shared\ValueObjects\LawVector;

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
