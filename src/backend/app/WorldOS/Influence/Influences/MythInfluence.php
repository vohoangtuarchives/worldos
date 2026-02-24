<?php

declare(strict_types=1);

namespace App\WorldOS\Influence\Influences;

use App\WorldOS\Influence\Contracts\EvolutionInfluenceInterface;
use App\WorldOS\Influence\ValueObjects\EvolutionContext;
use App\WorldOS\Influence\ValueObjects\VectorForce;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * Myth Influence — cultural pressure from active myths.
 *
 * Active myths boost cohesion (shared belief binds people)
 * and provide order (myths create social norms).
 * Strong myths also reduce innovation (tradition resists change).
 *
 * Pure computation.
 */
final class MythInfluence implements EvolutionInfluenceInterface
{
    private const MAX_DELTA = 0.04;

    public function apply(WorldStateVector $state, EvolutionContext $context): VectorForce
    {
        $myths = $context->activeMyths;

        if (empty($myths)) {
            return VectorForce::zero();
        }

        // Aggregate myth influence
        $totalInfluence = 0.0;
        foreach ($myths as $myth) {
            $totalInfluence += $myth->calculateInfluence();
        }

        $factor = min($totalInfluence, 1.0);

        return new VectorForce(
            deltaOrder: $factor * self::MAX_DELTA * 0.5,
            deltaCohesion: $factor * self::MAX_DELTA * 0.7,
            deltaInnovation: -$factor * self::MAX_DELTA * 0.3, // Tradition resists change
            deltaLegitimacy: $factor * self::MAX_DELTA * 0.4,
            source: 'myth',
        );
    }

    public function name(): string
    {
        return 'MythInfluence';
    }
}
