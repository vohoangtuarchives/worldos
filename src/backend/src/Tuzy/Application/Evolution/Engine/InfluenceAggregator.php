<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\Engine;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Domain\Evolution\EvolutionContext;
use Tuzy\Application\Evolution\Influence\EvolutionInfluence;
use Tuzy\Application\Evolution\Influence\InfluenceRegistry;
use Tuzy\Application\Evolution\Sensitivity\SensitivityMatrixInterface;
use Tuzy\Domain\Evolution\ValueObjects\VectorForce;
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
