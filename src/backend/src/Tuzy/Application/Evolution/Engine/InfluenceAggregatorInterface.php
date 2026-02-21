<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\Engine;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Domain\Evolution\EvolutionContext;
use Tuzy\Domain\Evolution\ValueObjects\VectorForce;

interface InfluenceAggregatorInterface
{
    public function aggregate(WorldStateVector $state, EvolutionContext $context): VectorForce;
}
