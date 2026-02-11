<?php

namespace App\StoryEngine\Levers;

use App\StoryEngine\FactionState;
use App\StoryEngine\InformationSeed;
use App\StoryEngine\Seed;

class PerceptionFilter
{
    /**
     * Filter the seeds that a Faction PERCEIVES.
     * Some seeds (InformationSeeds) might be ignored or believed based on cohesion.
     * 
     * @param Seed[] $events
     * @param FactionState $faction
     * @return Seed[] The filtered list of seeds the faction reacts to.
     */
    public static function apply(
        array $events,
        FactionState $faction
    ): array {
        $perceived = [];

        foreach ($events as $event) {
            if ($event instanceof InformationSeed) {
                // Check if faction detects the lie
                // Low cohesion = paranoid or gullible? 
                // Let's say Low Cohesion = Disorganized -> Gullible (Chaos)
                // Or High Cohesion = Trusting of internal, skeptical of external.
                
                // Simple View:
                // If Cohesion is low, they are easily manipulated.
                // If Truth is low (< 0.5) and Cohesion is low (< 50), they BELIEVE it (keep it).
                // If Truth is low but Cohesion is High, they DISCARD it (detect lie).
                
                if ($event->truthfulness < 0.5) {
                    if ($faction->cohesion > 60) {
                        // High cohesion, detects lie. Ignore it.
                        continue; 
                    }
                    // Else: Believes the lie. Add it.
                }
            }
            $perceived[] = $event;
        }

        return $perceived;
    }
}
