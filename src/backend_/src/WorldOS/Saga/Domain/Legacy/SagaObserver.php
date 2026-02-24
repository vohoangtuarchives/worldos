<?php

namespace WorldOS\Saga\Domain\Legacy;

/**
 * Saga Observer
 * 
 * Observes patterns across multiple worlds in a saga.
 * 
 * Observations:
 * 1. Patterns - What repeats across worlds
 * 2. Divergences - What differs despite same setup
 * 3. Archetype Shifts - How archetypes evolve across saga
 */
class SagaObserver
{
    /**
     * Observe a single world completion
     */
    public function observe(Saga $saga, SagaWorld $sagaWorld): void
    {
        // Detect immediate patterns
        $this->detectCollapsePatterns($saga, $sagaWorld);
        $this->detectArchetypePatterns($saga, $sagaWorld);
    }

    /**
     * Observe entire saga after completion
     */
    public function observeSaga(Saga $saga): void
    {
        $this->detectCrossWorldPatterns($saga);
        $this->detectDivergences($saga);
        $this->detectArchetypeEvolution($saga);
    }

    /**
     * Detect collapse patterns
     */
    private function detectCollapsePatterns(Saga $saga, SagaWorld $sagaWorld): void
    {
        if (!$sagaWorld->hasCollapsed()) {
            return;
        }

        // Check if same archetype caused collapse before
        $collapseContext = $sagaWorld->collapse_context;
        $dominantArchetype = $collapseContext['dominant_archetype'] ?? null;

        if (!$dominantArchetype) {
            return;
        }

        // Count previous collapses with same archetype
        $previousCollapses = $saga->sagaWorlds()
            ->where('status', SagaWorld::STATUS_COLLAPSED)
            ->where('sequence', '<', $sagaWorld->sequence)
            ->get()
            ->filter(function ($sw) use ($dominantArchetype) {
                return ($sw->collapse_context['dominant_archetype'] ?? null) === $dominantArchetype;
            })
            ->count();

        if ($previousCollapses > 0) {
            SagaObservation::create([
                'saga_id' => $saga->id,
                'observation_type' => SagaObservation::TYPE_PATTERN,
                'observation' => "Archetype '{$dominantArchetype}' caused collapse {$previousCollapses} times",
                'context' => [
                    'archetype' => $dominantArchetype,
                    'collapse_count' => $previousCollapses + 1,
                ],
                'confidence' => 0.8,
            ]);
        }
    }

    /**
     * Detect archetype patterns
     */
    private function detectArchetypePatterns(Saga $saga, SagaWorld $sagaWorld): void
    {
        $archetypeLegacy = $sagaWorld->archetype_legacy;

        if (!$archetypeLegacy) {
            return;
        }

        foreach ($archetypeLegacy as $archetypeKey => $legacy) {
            if ($legacy['type'] === 'dominance' && $legacy['intensity'] > 0.8) {
                SagaObservation::create([
                    'saga_id' => $saga->id,
                    'observation_type' => SagaObservation::TYPE_ARCHETYPE_SHIFT,
                    'observation' => "Archetype '{$archetypeKey}' achieved dominance",
                    'context' => [
                        'archetype' => $archetypeKey,
                        'world_sequence' => $sagaWorld->sequence,
                        'intensity' => $legacy['intensity'],
                    ],
                    'confidence' => 0.7,
                ]);
            }
        }
    }

    /**
     * Detect cross-world patterns
     */
    private function detectCrossWorldPatterns(Saga $saga): void
    {
        $worlds = $saga->getCompletedWorlds();

        // Pattern: Do same archetypes repeat?
        $archetypeOccurrences = [];

        foreach ($worlds as $sagaWorld) {
            $legacy = $sagaWorld->archetype_legacy ?? [];
            
            foreach ($legacy as $archetypeKey => $data) {
                if (!isset($archetypeOccurrences[$archetypeKey])) {
                    $archetypeOccurrences[$archetypeKey] = 0;
                }
                $archetypeOccurrences[$archetypeKey]++;
            }
        }

        // Report frequently recurring archetypes
        foreach ($archetypeOccurrences as $archetype => $count) {
            if ($count >= 3) {
                SagaObservation::create([
                    'saga_id' => $saga->id,
                    'observation_type' => SagaObservation::TYPE_PATTERN,
                    'observation' => "Archetype '{$archetype}' was significant in {$count} worlds",
                    'context' => [
                        'archetype' => $archetype,
                        'occurrence_count' => $count,
                        'total_worlds' => $worlds->count(),
                    ],
                    'confidence' => min(1.0, $count / $worlds->count()),
                ]);
            }
        }
    }

    /**
     * Detect divergences (same setup, different outcome)
     */
    private function detectDivergences(Saga $saga): void
    {
        $worlds = $saga->getCompletedWorlds();

        if ($worlds->count() < 2) {
            return;
        }

        // Compare outcomes
        $collapsed = $worlds->filter(fn($sw) => $sw->hasCollapsed())->count();
        $completed = $worlds->count() - $collapsed;

        if ($collapsed > 0 && $completed > 0) {
            SagaObservation::create([
                'saga_id' => $saga->id,
                'observation_type' => SagaObservation::TYPE_DIVERGENCE,
                'observation' => "Divergent outcomes: {$collapsed} collapsed, {$completed} survived",
                'context' => [
                    'collapsed' => $collapsed,
                    'survived' => $completed,
                ],
                'confidence' => 0.9,
            ]);
        }
    }

    /**
     * Detect archetype evolution across saga
     */
    private function detectArchetypeEvolution(Saga $saga): void
    {
        $worlds = $saga->sagaWorlds()->orderBy('sequence')->get();

        // Track how archetypes drift across worlds
        // (This would need more sophisticated analysis in full implementation)
    }
}
