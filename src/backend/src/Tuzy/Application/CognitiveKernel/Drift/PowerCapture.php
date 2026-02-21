<?php

namespace Tuzy\Application\CognitiveKernel\Drift;

use Tuzy\Domain\CognitiveKernel\ArchetypeWeight;
use App\Models\World;

/**
 * Power Capture
 * 
 * Elite/powerful factions monopolize interpretation of archetypes.
 * When power structures capture an archetype, it drifts toward serving power.
 * 
 * Example: "sacrifice" captured by elite → drifts toward "extractive" pole
 */
class PowerCapture
{
    /**
     * Calculate power-capture-induced drift
     * 
     * Positive drift when power monopolizes archetype interpretation
     */
    public function calculate(
        World $world,
        ArchetypeWeight $archetypeWeight,
        array $context
    ): float {
        $archetypeKey = $archetypeWeight->archetype_key;

        // Check if factions exist and if they're using this archetype
        if (!$world->relationLoaded('factions')) {
            $world->load('factions');
        }

        $factions = $world->factions;
        
        if ($factions->isEmpty()) {
            return 0;
        }

        $totalDrift = 0;

        foreach ($factions as $faction) {
            // Check if faction uses this archetype in their doctrine/identity
            $factionArchetypes = $faction->metadata['archetypes'] ?? [];
            
            if (!in_array($archetypeKey, $factionArchetypes)) {
                continue;
            }

            // Power level of faction
            $powerLevel = $faction->power_level ?? 0.5;
            
            // High power + archetype usage = capture
            if ($powerLevel > 0.7) {
                // Archetype drifts toward interpretation that serves power
                $captureIntensity = ($powerLevel - 0.7) / 0.3; // 0-1 scale
                $totalDrift += 0.03 * $captureIntensity;
            }
        }

        return min(0.05, $totalDrift);
    }
}
