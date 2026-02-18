<?php

declare(strict_types=1);

namespace App\Domains\Evolution\Influence;

use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Evolution\EvolutionContext;
use App\Domains\Evolution\ValueObjects\VectorForce;

/**
 * EvolutionInfluence - Vector field: produces a force from current state and context.
 * WorldOS 2.0 Clean: category() for InfluencePipeline ordering (Structural → Cultural → …).
 */
interface EvolutionInfluence
{
    public function force(WorldStateVector $state, EvolutionContext $context): VectorForce;

    public function priority(): int;

    public function isActive(WorldStateVector $state, EvolutionContext $context): bool;

    /** Category for pipeline application order. Default Cultural for backward compat. */
    public function category(): InfluenceCategory;
}
