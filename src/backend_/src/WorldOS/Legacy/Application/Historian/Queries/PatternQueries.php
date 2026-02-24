<?php

namespace WorldOS\Legacy\Application\Historian\Queries;

use WorldOS\Saga\Domain\Legacy\Saga;
use WorldOS\Saga\Domain\Legacy\SagaWorld;

/**
 * Pattern Queries
 * 
 * Constitutional Queries (ADR-1004):
 * ✅ ALLOWED: "What pattern repeated?"
 * ✅ ALLOWED: "What collapsed more than once?"
 * ❌ FORBIDDEN: "Which myth was correct?"
 */
class PatternQueries
{
    /**
     * What collapsed more than once?
     */
    public function whatCollapsedMultipleTimes(Saga $saga): array
    {
        $collapses = $saga->sagaWorlds()
            ->where('status', SagaWorld::STATUS_COLLAPSED)
            ->get();

        $triggers = [];

        foreach ($collapses as $collapse) {
            $archetype = $collapse->collapse_context['dominant_archetype'] ?? 'unknown';
            
            if (!isset($triggers[$archetype])) {
                $triggers[$archetype] = 0;
            }
            $triggers[$archetype]++;
        }

        // Filter to only repeated triggers
        return array_filter($triggers, fn($count) => $count > 1);
    }

    /**
     * What patterns repeated across worlds?
     */
    public function whatPatternsRepeated(Saga $saga): array
    {
        $worlds = $saga->sagaWorlds()->get();
        $archetypeOccurrences = [];

        foreach ($worlds as $world) {
            $legacy = $world->archetype_legacy ?? [];
            
            foreach ($legacy as $archetypeKey => $data) {
                if (!isset($archetypeOccurrences[$archetypeKey])) {
                    $archetypeOccurrences[$archetypeKey] = [
                        'count' => 0,
                        'sequences' => [],
                        'types' => [],
                    ];
                }

                $archetypeOccurrences[$archetypeKey]['count']++;
                $archetypeOccurrences[$archetypeKey]['sequences'][] = $world->sequence;
                $archetypeOccurrences[$archetypeKey]['types'][] = $data['type'];
            }
        }

        // Filter to patterns that occurred in 2+ worlds
        return array_filter($archetypeOccurrences, fn($data) => $data['count'] >= 2);
    }

    /**
     * Which archetypes dominated consistently?
     */
    public function whichArchetypesDominated(Saga $saga): array
    {
        $worlds = $saga->sagaWorlds()->get();
        $dominance = [];

        foreach ($worlds as $world) {
            $legacy = $world->archetype_legacy ?? [];
            
            foreach ($legacy as $archetypeKey => $data) {
                if ($data['type'] === 'dominance') {
                    if (!isset($dominance[$archetypeKey])) {
                        $dominance[$archetypeKey] = 0;
                    }
                    $dominance[$archetypeKey]++;
                }
            }
        }

        arsort($dominance);
        return $dominance;
    }

    /**
     * What sequence patterns exist in collapses?
     */
    public function whatCollapseSequenceExists(Saga $saga): array
    {
        $collapses = $saga->sagaWorlds()
            ->where('status', SagaWorld::STATUS_COLLAPSED)
            ->orderBy('sequence')
            ->get();

        $sequences = $collapses->pluck('sequence')->toArray();

        return [
            'sequences' => $sequences,
            'pattern' => $this->identifySequencePattern($sequences),
            'clustering' => $this->detectClustering($sequences),
        ];
    }

    /**
     * Identify sequence pattern
     */
    private function identifySequencePattern(array $sequences): string
    {
        if (empty($sequences)) {
            return 'none';
        }

        if (count($sequences) === 1) {
            return 'isolated';
        }

        // Check consecutive
        sort($sequences);
        $consecutive = true;

        for ($i = 1; $i < count($sequences); $i++) {
            if ($sequences[$i] !== $sequences[$i-1] + 1) {
                $consecutive = false;
                break;
            }
        }

        if ($consecutive) {
            return 'cascade';
        }

        // Check if early vs late
        $avg = array_sum($sequences) / count($sequences);
        
        if ($avg < 2) {
            return 'early_instability';
        } elseif ($avg > 3) {
            return 'late_pressure';
        }

        return 'scattered';
    }

    /**
     * Detect clustering in sequences
     */
    private function detectClustering(array $sequences): array
    {
        if (count($sequences) < 2) {
            return ['clustered' => false];
        }

        sort($sequences);
        $gaps = [];

        for ($i = 1; $i < count($sequences); $i++) {
            $gaps[] = $sequences[$i] - $sequences[$i-1];
        }

        $avgGap = array_sum($gaps) / count($gaps);

        return [
            'clustered' => $avgGap < 2,
            'avg_gap' => $avgGap,
        ];
    }
}
