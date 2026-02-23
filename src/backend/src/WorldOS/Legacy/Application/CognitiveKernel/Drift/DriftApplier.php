<?php

namespace WorldOS\Legacy\Application\CognitiveKernel\Drift;

use WorldOS\Legacy\Domain\CognitiveKernel\ArchetypeWeight;
use WorldOS\Legacy\Domain\CognitiveKernel\ArchetypeDriftLog;
use App\Models\World;

/**
 * Drift Applier
 * 
 * Applies calculated drift to archetype weights and logs changes
 */
class DriftApplier
{
    private DriftCalculator $calculator;

    public function __construct()
    {
        $this->calculator = new DriftCalculator();
    }

    /**
     * Apply drift to a single archetype
     */
    public function applyToArchetype(
        World $world,
        ArchetypeWeight $archetypeWeight
    ): void {
        $drift = $this->calculator->calculate($world, $archetypeWeight);

        if (abs($drift['delta']) < 0.001) {
            return; // Ignore negligible drift
        }

        // Record drift in history
        $archetypeWeight->recordDrift($drift['delta'], $drift['sources']);

        // Log drift
        ArchetypeDriftLog::create([
            'world_id' => $world->id,
            'archetype_key' => $archetypeWeight->archetype_key,
            'drift_delta' => $drift['delta'],
            'drift_sources' => $drift['sources'],
            'tick' => $world->tick ?? 0,
            'context' => [
                'weight_before' => $archetypeWeight->weight - $drift['delta'],
                'weight_after' => $archetypeWeight->weight,
            ]
        ]);
    }

    /**
     * Apply drift to all archetypes in a world
     */
    public function applyToWorld(World $world): array
    {
        $weights = ArchetypeWeight::where('world_id', $world->id)->get();
        $driftResults = [];

        foreach ($weights as $weight) {
            $this->applyToArchetype($world, $weight);
            
            $driftResults[] = [
                'archetype_key' => $weight->archetype_key,
                'new_weight' => $weight->weight
            ];
        }

        return $driftResults;
    }
}
