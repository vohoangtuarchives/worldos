<?php

namespace App\StoryEngine\Balancing;

use App\StoryEngine\WorldState;
use App\StoryEngine\Seed;

class BalancingApplier
{
    /**
    /**
     * @param WorldState $world
     * @param array $activeSeeds Reference to Simulator seeds array
     * @param \App\Domains\World\Services\WorldLawValidator $validator
     */
    public static function apply(WorldState $world, array &$activeSeeds, \App\Domains\World\Services\WorldLawValidator $validator): void
    {
        $health = WorldHealthCalculator::calculate($world);
        $danger = $health->dangerScore();

        // ADR-0004: Scale Influence by Heavenly Way Strength
        $influence = $validator->getBalancingInfluence($world->lawProfile); // e.g. 1.0, 0.5, 2.0
        
        // Adjust thresholds based on influence
        // High influence (2.0) -> Reacts sooner (Lower threshold for Truce, Higher for Ruin)
        // Danger > 70 normally. With 2.0 influence -> Danger > 35? No, that's too aggressive.
        // Let's say Influence acts as a probability multiplier for intervention?
        // OR checks effectively "Perceived Danger" = Danger * Influence?
        
        // ADR-0003 says: "effective_influence = heavenly_way_strength * WorldHealthDeviation"
        // Let's simply modify the probability of acting.
        $chanceToAct = 100 * $influence; // If 0.1, only 10% chance to intervene even if thresholds met?
        // Or thresholds shift.
        
        // Let's implement Probability Check first.
        if (rand(0, 100) > $chanceToAct) {
            return; // The Heavenly Way is weak/passive here.
        }

        // Rule 1: High Danger -> Inject Relief
        if ($danger > 70) {
            // "Heavenly Dao intervenes to preserve balance"
            // Inject a positive/stabilizing seed
            $activeSeeds[] = new Seed(
                'TEMPORARY_TRUCE',
                'world',
                3
            );
        }

        // Rule 2: Low Factions -> Pressure to spawn new ones?
        if ($health->activeFactions < 3) {
            // "Vacuum of power creates new challengers"
            // We can't spawn faction directly here (Simulator responsibility),
            // but we can add a seed that TRIGGERS creation.
            $activeSeeds[] = new Seed(
                'NEW_FACTION_RISE',
                'world',
                5
            );
        }
        
        // Rule 3: Too Peaceful -> Inject Conflict (Anti-Stagnation)
        if ($danger < 20) {
            $activeSeeds[] = new Seed(
                'ANCIENT_RUIN_DISCOVERY', // Creates greed/conflict
                'world',
                4
            );
        }
    }
}
