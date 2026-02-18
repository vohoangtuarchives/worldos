<?php

declare(strict_types=1);

namespace App\Domains\Evolution\Engine;

use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Evolution\EvolutionContext;
use App\Domains\Evolution\Influence\EvolutionInfluence;
use App\Domains\Evolution\Influence\InfluenceRegistry;
use App\Domains\Evolution\Sensitivity\SensitivityMatrixInterface;
use App\Domains\Evolution\ValueObjects\VectorForce;
use App\Models\World;

/**
 * InfluenceAggregator - Collects forces from all influences for a world, applies sensitivity matrix if set, sums to one VectorForce.
 */
final class InfluenceAggregator implements InfluenceAggregatorInterface
{
    public function __construct(
        private readonly InfluenceRegistry $registry,
        private readonly ?SensitivityMatrixInterface $sensitivityMatrix = null
    ) {
    }

    public function aggregate(WorldStateVector $state, EvolutionContext $context): VectorForce
    {
        $world = World::find($context->worldId);
        if ($world === null) {
            return VectorForce::zero();
        }

        $influences = $this->registry->resolveForWorld($world);
        $net = VectorForce::zero();

        foreach ($influences as $influence) {
            if (!$influence->isActive($state, $context)) {
                continue;
            }
            $f = $influence->force($state, $context);
            if ($this->sensitivityMatrix !== null) {
                $f = $this->sensitivityMatrix->apply($f, $context);
            }
            $net = $net->add($f);
        }

        return $net;
    }
}
