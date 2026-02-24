<?php

namespace WorldOS\Legacy\Domain\Historian;

use WorldOS\Saga\Domain\Legacy\Saga;
use WorldOS\Saga\Domain\Legacy\SagaWorld;

/**
 * Saga Analyzer
 * 
 * Read-only analysis of completed sagas.
 * 
 * Constitutional Constraint (ADR-1004):
 * Historian NEVER modifies world state, NEVER influences simulation,
 * ONLY observes and reports patterns.
 */
class SagaAnalyzer
{
    /**
     * Analyze a completed saga
     * 
     * @return array Analysis report
     */
    public function analyze(Saga $saga): array
    {
        if (!$saga->isComplete()) {
            throw new \InvalidArgumentException('Can only analyze completed sagas');
        }

        return [
            'saga_id' => $saga->id,
            'saga_name' => $saga->name,
            'summary' => $this->generateSummary($saga),
            'timeline' => $this->buildTimeline($saga),
            'collapse_analysis' => $this->analyzeCollapses($saga),
            'archetype_evolution' => $this->analyzeArchetypeEvolution($saga),
            'myth_patterns' => $this->analyzeMythPatterns($saga),
            'observations' => $saga->observations()->get()->toArray(),
        ];
    }

    /**
     * Generate saga summary
     */
    private function generateSummary(Saga $saga): array
    {
        $worlds = $saga->sagaWorlds()->get();
        $collapsed = $worlds->where('status', SagaWorld::STATUS_COLLAPSED)->count();
        $survived = $worlds->count() - $collapsed;

        return [
            'total_worlds' => $worlds->count(),
            'collapsed' => $collapsed,
            'survived' => $survived,
            'collapse_rate' => $worlds->count() > 0 ? $collapsed / $worlds->count() : 0,
            'duration' => $saga->started_at && $saga->completed_at 
                ? $saga->completed_at->diffInSeconds($saga->started_at) 
                : 0,
        ];
    }

    /**
     * Build saga timeline
     */
    private function buildTimeline(Saga $saga): array
    {
        return $saga->sagaWorlds()
            ->orderBy('sequence')
            ->get()
            ->map(function ($sagaWorld) {
                return [
                    'sequence' => $sagaWorld->sequence,
                    'world_id' => $sagaWorld->world_id,
                    'status' => $sagaWorld->status,
                    'collapsed' => $sagaWorld->hasCollapsed(),
                    'archetype_legacy' => $sagaWorld->archetype_legacy,
                    'timestamp' => $sagaWorld->created_at->toIso8601String(),
                ];
            })
            ->toArray();
    }

    /**
     * Analyze collapse patterns
     */
    private function analyzeCollapses(Saga $saga): array
    {
        $collapses = $saga->sagaWorlds()
            ->where('status', SagaWorld::STATUS_COLLAPSED)
            ->get();

        if ($collapses->isEmpty()) {
            return [
                'count' => 0,
                'patterns' => [],
                'common_archetypes' => [],
            ];
        }

        // Find common archetype patterns in collapses
        $archetypeFrequency = [];

        foreach ($collapses as $collapse) {
            $dominantArchetype = $collapse->collapse_context['dominant_archetype'] ?? null;
            
            if ($dominantArchetype) {
                if (!isset($archetypeFrequency[$dominantArchetype])) {
                    $archetypeFrequency[$dominantArchetype] = 0;
                }
                $archetypeFrequency[$dominantArchetype]++;
            }
        }

        arsort($archetypeFrequency);

        return [
            'count' => $collapses->count(),
            'sequences' => $collapses->pluck('sequence')->toArray(),
            'common_archetypes' => $archetypeFrequency,
            'patterns' => $this->detectCollapsePatterns($collapses),
        ];
    }

    /**
     * Detect patterns in collapses
     */
    private function detectCollapsePatterns($collapses): array
    {
        $patterns = [];

        // Pattern: Sequential collapses
        $sequences = $collapses->pluck('sequence')->sort()->values()->toArray();
        $isSequential = false;
        
        for ($i = 1; $i < count($sequences); $i++) {
            if ($sequences[$i] === $sequences[$i-1] + 1) {
                $isSequential = true;
                break;
            }
        }

        if ($isSequential) {
            $patterns[] = 'Sequential collapses detected (cascade effect)';
        }

        // Pattern: Early vs late collapses
        $avgSequence = collect($sequences)->avg();
        
        if ($avgSequence < 2) {
            $patterns[] = 'Early collapse pattern (initial instability)';
        } elseif ($avgSequence > 3) {
            $patterns[] = 'Late collapse pattern (accumulated pressure)';
        }

        return $patterns;
    }

    /**
     * Analyze archetype evolution across saga
     */
    private function analyzeArchetypeEvolution(Saga $saga): array
    {
        $worlds = $saga->sagaWorlds()->orderBy('sequence')->get();
        $evolution = [];

        foreach ($worlds as $world) {
            $legacy = $world->archetype_legacy ?? [];
            
            foreach ($legacy as $archetypeKey => $data) {
                if (!isset($evolution[$archetypeKey])) {
                    $evolution[$archetypeKey] = [];
                }

                $evolution[$archetypeKey][] = [
                    'sequence' => $world->sequence,
                    'type' => $data['type'],
                    'intensity' => $data['intensity'],
                ];
            }
        }

        return $evolution;
    }

    /**
     * Analyze myth patterns
     */
    private function analyzeMythPatterns(Saga $saga): array
    {
        $worlds = $saga->sagaWorlds()->get();
        $mythPatterns = [];

        foreach ($worlds as $sagaWorld) {
            $mythLegacy = $sagaWorld->myth_legacy ?? [];
            
            foreach ($mythLegacy as $myth) {
                $doctrine = $myth['doctrine'] ?? 'unknown';
                
                if (!isset($mythPatterns[$doctrine])) {
                    $mythPatterns[$doctrine] = [
                        'count' => 0,
                        'avg_strength' => 0,
                        'residue_types' => [],
                    ];
                }

                $mythPatterns[$doctrine]['count']++;
                $mythPatterns[$doctrine]['residue_types'][] = $myth['residue_type'] ?? 'neutral';
            }
        }

        return $mythPatterns;
    }
}
