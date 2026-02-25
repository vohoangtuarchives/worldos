<?php

declare(strict_types=1);

namespace App\Modules\WorldTemplate\Contracts;

use App\Modules\Shared\ValueObjects\LawVector;
use App\Modules\Shared\ValueObjects\WorldStateVector;

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
