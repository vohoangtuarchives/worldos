<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\Influence;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Domain\Evolution\EvolutionContext;
use Tuzy\Domain\Evolution\ValueObjects\VectorForce;

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
