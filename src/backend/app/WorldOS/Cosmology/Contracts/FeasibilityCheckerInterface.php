<?php

declare(strict_types=1);

namespace App\WorldOS\Cosmology\Contracts;

use App\WorldOS\Cosmology\ValueObjects\FeasibilityResult;
use App\WorldOS\Shared\ValueObjects\LawVector;

/**
 * Feasibility Checker Contract.
 *
 * Validates F(θ) — determines whether a LawVector can produce a viable Universe.
 * Implementation lives in SimulationEngine bounded context.
 */
interface FeasibilityCheckerInterface
{
    public function check(LawVector $law): FeasibilityResult;
}
