<?php

declare(strict_types=1);

namespace App\WorldOS\Influence\Contracts;

use App\WorldOS\Influence\ValueObjects\EvolutionContext;
use App\WorldOS\Influence\ValueObjects\VectorForce;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * Evolution Influence Contract.
 *
 * From docs §16.4: EvolutionInfluence::apply(Vector $v, EvolutionContext $ctx): VectorForce
 *
 * Each influence reads the current state + context and returns
 * a VectorForce representing its effect on the civilization.
 */
interface EvolutionInfluenceInterface
{
    /**
     * Calculate the force this influence exerts on the state vector.
     */
    public function apply(WorldStateVector $state, EvolutionContext $context): VectorForce;

    /**
     * Get the name/category of this influence (for logging/audit).
     */
    public function name(): string;
}
