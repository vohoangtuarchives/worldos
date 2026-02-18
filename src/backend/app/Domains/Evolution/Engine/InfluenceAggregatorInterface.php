<?php

declare(strict_types=1);

namespace App\Domains\Evolution\Engine;

use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Evolution\EvolutionContext;
use App\Domains\Evolution\ValueObjects\VectorForce;

interface InfluenceAggregatorInterface
{
    public function aggregate(WorldStateVector $state, EvolutionContext $context): VectorForce;
}
