<?php

declare(strict_types=1);

namespace App\Domains\Evolution\Influence;

use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Evolution\EvolutionContext;
use App\Domains\Evolution\ValueObjects\VectorForce;
use App\Domains\Vietnamese\Services\RealmContactService;

/**
 * RealmContactInfluence - Maps realm contact modifiers to VectorForce.
 * military_pressure -> military+, strain (entropy+); cultural_assimilation -> cohesion-; trade_bonus -> resource_stock+; instability -> legitimacy-.
 */
final class RealmContactInfluence implements EvolutionInfluence
{
    private const SCALE = 0.015;

    public function __construct(
        private readonly RealmContactService $realmContact
    ) {
    }

    public function force(WorldStateVector $state, EvolutionContext $context): VectorForce
    {
        $era = (int) floor($context->year / 50);
        $mods = $this->realmContact->calculateRealmInfluenceForEra($era);

        $components = array_fill_keys(WorldStateVector::dimensions(), 0.0);

        if (!empty($mods['military_pressure'])) {
            $components[WorldStateVector::DIMENSION_MILITARY] += $mods['military_pressure'] * self::SCALE;
            $components[WorldStateVector::DIMENSION_ENTROPY] += $mods['military_pressure'] * self::SCALE * 0.3;
        }
        if (!empty($mods['cultural_assimilation'])) {
            $components[WorldStateVector::DIMENSION_COHESION] -= $mods['cultural_assimilation'] * 0.2 * self::SCALE;
        }
        if (!empty($mods['trade_bonus'])) {
            $components[WorldStateVector::DIMENSION_RESOURCE_STOCK] += $mods['trade_bonus'] * self::SCALE;
        }
        if (!empty($mods['instability'])) {
            $components[WorldStateVector::DIMENSION_LEGITIMACY] -= $mods['instability'] * self::SCALE;
        }

        return new VectorForce($components);
    }

    public function priority(): int
    {
        return 90;
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
