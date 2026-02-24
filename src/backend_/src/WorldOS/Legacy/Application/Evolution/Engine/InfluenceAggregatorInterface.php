<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\Engine;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Evolution\Domain\Legacy\EvolutionContext;
use WorldOS\Evolution\Domain\Legacy\ValueObjects\VectorForce;

interface InfluenceAggregatorInterface
{
    public function aggregate(WorldStateVector $state, EvolutionContext $context): VectorForce;
}
