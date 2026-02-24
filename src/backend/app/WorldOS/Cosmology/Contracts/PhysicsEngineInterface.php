<?php

declare(strict_types=1);

namespace App\WorldOS\Cosmology\Contracts;

use App\WorldOS\Shared\ValueObjects\LawVector;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * Physics Engine Contract.
 *
 * Evolves WorldStateVector by one tick using deterministic equations.
 * Implementation lives in SimulationEngine bounded context.
 */
interface PhysicsEngineInterface
{
    public function evolve(WorldStateVector $state, LawVector $law): WorldStateVector;
}
