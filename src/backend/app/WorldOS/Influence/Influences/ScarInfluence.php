<?php

declare(strict_types=1);

namespace App\WorldOS\Influence\Influences;

use App\WorldOS\Influence\Contracts\EvolutionInfluenceInterface;
use App\WorldOS\Influence\ValueObjects\EvolutionContext;
use App\WorldOS\Influence\ValueObjects\VectorForce;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * Scar Influence — historical trauma pressure on civilization.
 *
 * Accumulated scars increase entropy, decrease cohesion, and add trauma.
 * The effect is proportional to total scar pressure at current tick.
 *
 * From docs §8.4: "Power ∝ Scar Accumulation; Control ∝ 1 / Complexity"
 *
 * Pure computation — reads from EvolutionContext, no side effects.
 */
final class ScarInfluence implements EvolutionInfluenceInterface
{
    /**
     * Maximum influence per tick from scars.
     */
    private const MAX_DELTA = 0.05;

    public function apply(WorldStateVector $state, EvolutionContext $context): VectorForce
    {
        $pressure = $context->scarPressure;

        if ($pressure <= 0.0) {
            return VectorForce::zero();
        }

        // Scars push toward entropy and trauma, away from cohesion
        $factor = min($pressure, 1.0);

        return new VectorForce(
            deltaEntropy: $factor * self::MAX_DELTA * 0.6,
            deltaCohesion: -$factor * self::MAX_DELTA * 0.4,
            deltaTrauma: $factor * self::MAX_DELTA * 0.8,
            source: 'scar',
        );
    }

    public function name(): string
    {
        return 'ScarInfluence';
    }
}
