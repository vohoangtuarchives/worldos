<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\Influence;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Domain\Evolution\EvolutionContext;
use Tuzy\Domain\Evolution\ValueObjects\VectorForce;
use Tuzy\Application\Vietnamese\Services\CosmicIntegrationService;

/**
 * VietnameseHeroInfluence - Maps hero-era boosts to VectorForce dimensions.
 * Governance -> cohesion/legitimacy; culture/philosophy -> innovation; military -> entropy resistance (legitimacy/military); education -> innovation.
 */
final class VietnameseHeroInfluence implements EvolutionInfluence
{
    private const SCALE = 0.02;

    public function __construct(
        private readonly CosmicIntegrationService $cosmicIntegration
    ) {
    }

    public function force(WorldStateVector $state, EvolutionContext $context): VectorForce
    {
        $era = (int) floor($context->year / 50);
        $boosts = $this->cosmicIntegration->calculateEraCivilizationBoost($era);

        $components = array_fill_keys(WorldStateVector::dimensions(), 0.0);

        if (!empty($boosts['governance'])) {
            $components[WorldStateVector::DIMENSION_COHESION] += $boosts['governance'] * self::SCALE;
            $components[WorldStateVector::DIMENSION_LEGITIMACY] += $boosts['governance'] * self::SCALE;
        }
        if (!empty($boosts['culture']) || !empty($boosts['philosophy'])) {
            $components[WorldStateVector::DIMENSION_INNOVATION] += (($boosts['culture'] ?? 0) + ($boosts['philosophy'] ?? 0)) * self::SCALE;
        }
        if (!empty($boosts['military'])) {
            $components[WorldStateVector::DIMENSION_MILITARY] += $boosts['military'] * self::SCALE;
            $components[WorldStateVector::DIMENSION_ENTROPY] -= $boosts['military'] * self::SCALE * 0.5;
        }
        if (!empty($boosts['education'])) {
            $components[WorldStateVector::DIMENSION_INNOVATION] += $boosts['education'] * self::SCALE;
        }
        if (!empty($boosts['spirituality'])) {
            $components[WorldStateVector::DIMENSION_COHESION] += $boosts['spirituality'] * self::SCALE;
        }

        return new VectorForce($components);
    }

    public function priority(): int
    {
        return 100;
    }

    public function isActive(WorldStateVector $state, EvolutionContext $context): bool
    {
        return true;
    }

    public function category(): InfluenceCategory
    {
        return InfluenceCategory::Cultural;
    }
}
