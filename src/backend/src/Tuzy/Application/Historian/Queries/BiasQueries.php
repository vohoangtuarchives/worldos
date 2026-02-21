<?php

namespace Tuzy\Application\Historian\Queries;

use Tuzy\Domain\Saga\Saga;
use Tuzy\Domain\CognitiveKernel\ArchetypeWeight;

/**
 * Bias Queries
 * 
 * Constitutional Queries:
 * ✅ ALLOWED: "What archetype increased weight?"
 * ✅ ALLOWED: "What bias accumulated?"
 * ❌ FORBIDDEN: "What should have happened?"
 */
class BiasQueries
{
    /**
     * Which archetypes increased weight?
     */
    public function whichArchetypesIncreased(Saga $saga): array
    {
        $worlds = $saga->sagaWorlds()->get();
        $weightChanges = [];

        foreach ($worlds as $sagaWorld) {
            $world = $sagaWorld->world;
            
            if (!$world) {
                continue;
            }

            $weights = ArchetypeWeight::where('world_id', $world->id)->get();

            foreach ($weights as $weight) {
                $archetype = $weight->archetype();
                
                if (!$archetype) {
                    continue;
                }

                $change = $weight->weight - $archetype->baseline_weight;

                if ($change > 0.1) { // Significant increase
                    if (!isset($weightChanges[$weight->archetype_key])) {
                        $weightChanges[$weight->archetype_key] = [
                            'total_change' => 0,
                            'occurrences' => 0,
                        ];
                    }

                    $weightChanges[$weight->archetype_key]['total_change'] += $change;
                    $weightChanges[$weight->archetype_key]['occurrences']++;
                }
            }
        }

        return $weightChanges;
    }

    /**
     * Which archetypes were suppressed?
     */
    public function whichArchetypesSuppressed(Saga $saga): array
    {
        $worlds = $saga->sagaWorlds()->get();
        $suppressions = [];

        foreach ($worlds as $sagaWorld) {
            $world = $sagaWorld->world;
            
            if (!$world) {
                continue;
            }

            $weights = ArchetypeWeight::where('world_id', $world->id)
                ->where('weight', '<', 0.2)
                ->get();

            foreach ($weights as $weight) {
                if (!isset($suppressions[$weight->archetype_key])) {
                    $suppressions[$weight->archetype_key] = 0;
                }
                $suppressions[$weight->archetype_key]++;
            }
        }

        return $suppressions;
    }

    /**
     * What bias accumulated over saga?
     */
    public function whatBiasAccumulated(Saga $saga): array
    {
        $firstWorld = $saga->sagaWorlds()->orderBy('sequence')->first();
        $lastWorld = $saga->sagaWorlds()->orderBy('sequence', 'desc')->first();

        if (!$firstWorld || !$lastWorld || !$firstWorld->world || !$lastWorld->world) {
            return [];
        }

        $initialWeights = ArchetypeWeight::where('world_id', $firstWorld->world->id)->get();
        $finalWeights = ArchetypeWeight::where('world_id', $lastWorld->world->id)->get();

        $biasAccumulation = [];

        foreach ($finalWeights as $finalWeight) {
            $initialWeight = $initialWeights->firstWhere('archetype_key', $finalWeight->archetype_key);

            if (!$initialWeight) {
                continue;
            }

            $delta = $finalWeight->weight - $initialWeight->weight;

            if (abs($delta) > 0.15) { // Significant bias
                $biasAccumulation[$finalWeight->archetype_key] = [
                    'initial' => $initialWeight->weight,
                    'final' => $finalWeight->weight,
                    'delta' => $delta,
                    'direction' => $delta > 0 ? 'increased' : 'decreased',
                ];
            }
        }

        return $biasAccumulation;
    }
}
