<?php

declare(strict_types=1);

namespace App\Domains\Mutation\Contracts;

use App\Domains\Cosmology\Entities\WorldStateVector;

/**
 * Describes the permitted delta to apply to Universe (vector delta, optional pressure/attractor).
 */
interface WorldMutationContract
{
    public function vectorDelta(): WorldStateVector;

    public function pressureDelta(): float;
}
