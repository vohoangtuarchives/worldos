<?php

namespace App\Domains\Historian;

use App\Domains\Saga\Saga;
use App\Domains\Saga\SagaObservation;

/**
 * Archetype Analyzer
 * 
 * Analyzing archetype trends and evolution across sagas.
 */
class ArchetypeAnalyzer
{
    /**
     * Analyze a specific archetype's history across sagas
     */
    public function analyzeArchetype(string $archetypeKey, array $sagaIds = []): array
    {
        $query = Saga::query()->where('status', Saga::STATUS_COMPLETED);
        
        if (!empty($sagaIds)) {
            $query->whereIn('id', $sagaIds);
        }

        $sagas = $query->get();
        $totalSagas = $sagas->count();
        $occurrences = 0;
        $dominanceCount = 0;
        $collapseInvolvement = 0;

        foreach ($sagas as $saga) {
            // Check if archetype appeared in legacy
            $appeared = false;
            foreach ($saga->sagaWorlds as $world) {
                if (isset($world->archetype_legacy[$archetypeKey])) {
                    $appeared = true;
                    if ($world->archetype_legacy[$archetypeKey]['type'] === 'dominance') {
                        $dominanceCount++;
                    }
                }
                
                // Check collapse context
                if ($world->hasCollapsed() && 
                    ($world->collapse_context['dominant_archetype'] ?? '') === $archetypeKey) {
                    $collapseInvolvement++;
                }
            }
            if ($appeared) $occurrences++;
        }

        return [
            'key' => $archetypeKey,
            'total_analyzed' => $totalSagas,
            'appearance_rate' => $totalSagas > 0 ? $occurrences / $totalSagas : 0,
            'dominance_rate' => $totalSagas > 0 ? $dominanceCount / ($totalSagas * 5) : 0, // Approx 5 worlds per saga
            'collapse_rate' => $occurrences > 0 ? $collapseInvolvement / $occurrences : 0,
        ];
    }

    /**
     * Generate Heatmap Data
     * Returns a matrix of [Archetype x Time/Sequence] -> Intensity
     */
    public function generateHeatmap(Saga $saga): array
    {
        $matrix = [];
        $worlds = $saga->sagaWorlds()->orderBy('sequence')->get();
        
        // Collect all archetypes that appear
        $allArchetypes = [];
        foreach ($worlds as $world) {
            $legacy = $world->archetype_legacy ?? [];
            foreach (array_keys($legacy) as $key) {
                $allArchetypes[$key] = true;
            }
        }
        $archetypes = array_keys($allArchetypes);

        // Build matrix
        foreach ($archetypes as $arch) {
            $row = [];
            foreach ($worlds as $world) {
                $intensity = 0;
                if (isset($world->archetype_legacy[$arch])) {
                    $intensity = $world->archetype_legacy[$arch]['intensity'];
                }
                $row[] = $intensity;
            }
            $matrix[$arch] = $row;
        }

        return [
            'sequences' => $worlds->pluck('sequence')->toArray(),
            'matrix' => $matrix
        ];
    }
}
