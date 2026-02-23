<?php

namespace WorldOS\Legacy\Application\CognitiveKernel\Drift;

use WorldOS\Legacy\Domain\CognitiveKernel\ArchetypeWeight;
use App\Models\World;

/**
 * Trauma Residue
 * 
 * Legacy from past collapses, scars, and painful events creates bias.
 * Archetypes associated with trauma may drift based on how trauma is remembered.
 * 
 * Example: If "silence" is associated with oppression scars, weight may drift down
 */
class TraumaResidue
{
    /**
     * Calculate trauma-induced drift
     * 
     * Can be positive or negative depending on how trauma is processed
     */
    public function calculate(
        World $world,
        ArchetypeWeight $archetypeWeight,
        array $context
    ): float {
        $archetypeKey = $archetypeWeight->archetype_key;

        // Count scars associated with this archetype
        $scarsWithArchetype = $world->scars()
            ->where(function ($query) use ($archetypeKey) {
                $query->whereJsonContains('metadata->related_archetypes', $archetypeKey)
                    ->orWhere('trigger_context', 'like', "%{$archetypeKey}%");
            })
            ->get();

        if ($scarsWithArchetype->isEmpty()) {
            return 0;
        }

        $totalDrift = 0;

        foreach ($scarsWithArchetype as $scar) {
            $severity = $scar->severity ?? 0.5;
            
            // Trauma can cause:
            // 1. Avoidance (negative drift) - society moves away
            // 2. Obsession (positive drift) - society fixates
            
            // Use scar type to determine direction
            $traumaType = $scar->metadata['trauma_type'] ?? 'avoidance';
            
            if ($traumaType === 'obsession') {
                $totalDrift += 0.02 * $severity;
            } else {
                $totalDrift -= 0.03 * $severity;
            }
        }

        return max(-0.05, min(0.05, $totalDrift));
    }
}
