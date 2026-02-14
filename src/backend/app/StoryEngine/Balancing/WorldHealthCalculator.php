<?php

namespace App\StoryEngine\Balancing;

use App\StoryEngine\WorldState;

class WorldHealthCalculator
{
    public static function calculate(WorldState $world): WorldHealth
    {
        // 1. Economic Stability: Average of Faction Resources (simplified to 100 - stress)
        $totalStress = 0;
        $factionCount = count($world->factions);
        
        foreach ($world->factions as $f) {
            $totalStress += $f->economy->stressLevel();
        }
        $avgStress = $factionCount > 0 ? ($totalStress / $factionCount) : 0;
        $economicStability = max(0, 100 - $avgStress);

        // 2. Conflict Level: Based on Public Awareness + Active Seeds count?
        // Or "Trauma" count?
        // Let's use Public Awareness as proxy for chaos for now, or define a new metric.
        // Or aggregate Faction Trauma.
        $conflictLevel = min(100, $world->publicAwareness); 

        // 3. Population Stress: World-level stress?
        // Maybe derived from Conflict + Economy
        $populationStress = ($economicStability < 50) ? (100 - $economicStability) : 0;
        $populationStress += ($conflictLevel > 50) ? ($conflictLevel - 50) : 0;
        $populationStress = min(100, $populationStress);

        return new WorldHealth(
            activeFactions: $factionCount,
            economicStability: (int)$economicStability,
            conflictLevel: (int)$conflictLevel,
            populationStress: (int)$populationStress
        );
    }
}
