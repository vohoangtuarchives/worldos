<?php

namespace App\Domains\Faction\Policies;

use App\Models\Faction;
use App\Models\World;
use WorldOS\Society\Faction\ValueObject\PersonalityVector;
use WorldOS\Society\Faction\ValueObject\Leader;

class CivilWarPolicy
{
    /**
     * Check if a faction should split.
     */
    public function shouldSplit(Faction $faction): bool
    {
        // 1. Check Cohesion
        $cohesion = $faction->internal_cohesion ?? 100;
        if ($cohesion > 30) {
            return false;
        }

        // 2. Check Stability (Personality)
        $personality = $faction->getPersonality();
        $stability = $personality->rationality + (1.0 - $personality->fear);
        
        // If stable leader, they can hold it together even with low cohesion
        if ($stability > 1.2) {
            return false;
        }

        // 3. Random Chance (Chaos factor)
        // 15% chance per tick if conditions met
        return (mt_rand(0, 100) < 15);
    }

    /**
     * Execute the split.
     * Returns the new rebel faction.
     */
    public function executeSplit(Faction $faction): Faction
    {
        $rebelName = "Separatists of " . $faction->name;
        
        $rebel = Faction::create([
            'world_id' => $faction->world_id,
            'name' => $rebelName,
            'type' => 'rebellion',
            'attributes' => [
                'military' => ($faction->attributes['military'] ?? 0) * 0.4,
                'resources' => ($faction->attributes['resources'] ?? 0) * 0.3,
            ],
            'internal_cohesion' => 50,
            // Rebels inherit parent ideology but usually identical initially, 
            // will drift later.
            'ideology_vector' => $faction->ideology_vector, 
            // Rebel leader is random and likely aggressive
            'personality_vector' => PersonalityVector::random()->toArray(),
            'leader_data' => Leader::create(mt_rand(20, 40), $faction->current_generation)->toArray(),
        ]);

        // Weaken original faction
        $attrs = $faction->attributes ?? [];
        $attrs['military'] = ($attrs['military'] ?? 0) * 0.6;
        $attrs['resources'] = ($attrs['resources'] ?? 0) * 0.7;
        $faction->attributes = $attrs;
        
        // Reset Cohesion partially as dissenters have left
        $faction->internal_cohesion = 60; 
        $faction->save();

        return $rebel;
    }
}
