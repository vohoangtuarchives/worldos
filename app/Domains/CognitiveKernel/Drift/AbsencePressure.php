<?php

namespace App\Domains\CognitiveKernel\Drift;

use App\Domains\CognitiveKernel\ArchetypeWeight;
use App\Models\World;

/**
 * Absence Pressure
 * 
 * Suppression or absence of an archetype causes pendulum swing.
 * When an archetype is systematically avoided, pressure builds for its return.
 * 
 * Example: "rebellion" suppressed → weight slowly increases until eruption
 */
class AbsencePressure
{
    /**
     * Calculate absence-induced drift
     * 
     * Positive drift when archetype is suppressed (pressure to return)
     */
    public function calculate(
        World $world,
        ArchetypeWeight $archetypeWeight,
        array $context
    ): float {
        $archetypeKey = $archetypeWeight->archetype_key;
        $currentWeight = $archetypeWeight->weight;

        // If weight is very low, check if it's being suppressed
        if ($currentWeight < 0.2) {
            // Count recent events/myths that explicitly avoid this archetype
            $recentTicks = 10;
            $currentTick = $context['tick'] ?? 0;
            
            $suppressionEvents = $world->events()
                ->where('tick', '>', max(0, $currentTick - $recentTicks))
                ->where(function ($query) use ($archetypeKey) {
                    $query->whereJsonContains('metadata->suppressed_archetypes', $archetypeKey)
                        ->orWhere('metadata->avoided_archetype', $archetypeKey);
                })
                ->count();

            if ($suppressionEvents > 0) {
                // Build pressure for return (positive drift)
                $pressure = 0.02 * min(1, $suppressionEvents / 5);
                return $pressure;
            }

            // Even without explicit suppression, very low weight builds passive pressure
            if ($currentWeight < 0.1) {
                return 0.01; // Slow return pressure
            }
        }

        return 0;
    }
}
