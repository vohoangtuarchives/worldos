<?php

namespace App\Domains\CognitiveKernel\Drift;

use App\Domains\CognitiveKernel\ArchetypeWeight;
use App\Models\World;

/**
 * Repetition Pressure
 * 
 * Overuse of an archetype causes it to lose meaning.
 * The more a myth using this archetype is repeated, the weaker the archetype becomes.
 * 
 * Example: "sacrifice" invoked too often becomes ritual, loses sacred weight
 */
class RepetitionPressure
{
    /**
     * Calculate repetition-induced drift
     * 
     * Negative drift when archetype is overused
     */
    public function calculate(
        World $world,
        ArchetypeWeight $archetypeWeight,
        array $context
    ): float {
        $archetypeKey = $archetypeWeight->archetype_key;

        // Count myths that use this archetype
        $mythCount = $world->myths()
            ->where(function ($query) use ($archetypeKey) {
                $query->whereJsonContains('metadata->archetypes', $archetypeKey)
                    ->orWhereJsonContains('metadata->dominant_archetype', $archetypeKey);
            })
            ->count();

        // If no myths use this archetype, no repetition pressure
        if ($mythCount === 0) {
            return 0;
        }

        // Calculate repetition rate
        $totalMyths = $world->myths()->count();
        $repetitionRate = $totalMyths > 0 ? $mythCount / $totalMyths : 0;

        // High repetition causes negative drift (loses meaning)
        if ($repetitionRate > 0.3) { // Over 30% of myths use this archetype
            $pressure = -0.05 * ($repetitionRate - 0.3); // Scale pressure
            return $pressure;
        }

        return 0;
    }
}
