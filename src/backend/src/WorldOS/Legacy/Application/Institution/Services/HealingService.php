<?php

namespace WorldOS\Legacy\Application\Institution\Services;

use App\Models\Institution;
use App\Models\Scar;
use App\Models\HealingEvent;
use App\Models\ScarCounterforce;
use Illuminate\Support\Str;

class HealingService
{
    /**
     * An institution attempts to heal a specific scar.
     * 
     * @param Institution $institution The regulator performing the action
     * @param Scar $scar The target history to neutralize
     * @param int $currentTick Current simulation time
     * @param array $methodologyVector { "ritual": 0.8, "propaganda": 0.2 }
     * @return HealingEvent The record of the attempt
     */
    public function performHealing(
        Institution $institution, 
        Scar $scar, 
        int $currentTick,
        array $methodologyVector
    ): HealingEvent
    {
        // 1. Calculate Effectiveness
        // Base power comes from authority * trust
        $basePower = $institution->authority_level * $institution->public_trust;
        
        // Methodology bonus? (Could depend on scar type, e.g., using "Ritual" for "Spiritual" scar)
        // For now, simple multiplier.
        $effectiveness = $basePower * 1.5; 

        // 2. Create Healing Event Record
        $event = HealingEvent::create([
            'id' => Str::uuid(),
            'institution_id' => $institution->id,
            'target_scar_id' => $scar->id,
            'effectiveness_score' => $effectiveness,
            'methodology_vector' => $methodologyVector,
            'tick' => $currentTick,
        ]);

        // 3. Generate Counterforce against the Scar
        // The healing vector opposes the scar's belief shift.
        // If scar shifts +militarism, healing should apply -militarism (or neutralize it).
        // Actually, ScarImpactService logic subtracts healing from impact. 
        // So we just need to provide the MAGNITUDE of healing in the same direction 
        // that we want to reduce.
        
        $healingVector = [];
        foreach ($scar->belief_shift_vector as $axis => $value) {
            // We want to reduce the absolute impact.
            // If impact is +0.5, healing of 0.2 makes it 0.3.
            // ScarImpactService: effective = max(0, impact - healing)
            // So healing vector should be positive magnitude for that axis.
            
            $healingVector[$axis] = abs($value) * ($effectiveness / 5.0); // effectiveness scales down
        }

        ScarCounterforce::create([
            'id' => Str::uuid(),
            'scar_id' => $scar->id,
            'origin_event_id' => $event->id,
            'healing_vector' => $healingVector,
            'strength' => $effectiveness,
            'created_tick' => $currentTick,
        ]);

        return $event;
    }
}
