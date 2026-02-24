<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\Engine;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Evolution\Domain\Legacy\EvolutionContext;
use WorldOS\Legacy\Application\Evolution\Influence\EvolutionInfluence;
use WorldOS\Legacy\Application\Evolution\Influence\InfluenceCategory;
use WorldOS\Legacy\Application\Evolution\Influence\InfluenceRegistry;
use WorldOS\Legacy\Application\Evolution\Sensitivity\SensitivityMatrixInterface;
use WorldOS\Evolution\Domain\Legacy\ValueObjects\VectorForce;
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
