<?php

declare(strict_types=1);

namespace App\Domains\Evolution\Engine;

use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Evolution\EvolutionContext;
use App\Domains\Evolution\Influence\EvolutionInfluence;
use App\Domains\Evolution\Influence\InfluenceCategory;
use App\Domains\Evolution\Influence\InfluenceRegistry;
use App\Domains\Evolution\Sensitivity\SensitivityMatrixInterface;
use App\Domains\Evolution\ValueObjects\VectorForce;
use App\Models\World;

/**
 * WorldOS 2.0 Clean: Aggregate influences by category order (Structural → Cultural → …).
 * Same contract as InfluenceAggregator; applies influences in category order then by priority within category.
 */
final class InfluencePipeline implements InfluenceAggregatorInterface
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
        usort($influences, function (EvolutionInfluence $a, EvolutionInfluence $b): int {
            $catOrder = $a->category()->order() <=> $b->category()->order();
            return $catOrder !== 0 ? $catOrder : $b->priority() <=> $a->priority();
        });

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
