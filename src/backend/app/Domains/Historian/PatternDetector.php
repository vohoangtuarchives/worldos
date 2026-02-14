<?php

namespace App\Domains\Historian;

use App\Domains\Saga\Saga;
use App\Domains\Saga\SagaWorld;
use App\Domains\Saga\SagaObservation;

/**
 * Pattern Detector
 * 
 * Detects recurring patterns across sagas.
 * 
 * Pattern Types:
 * 1. Archetype patterns - Which archetypes appear repeatedly
 * 2. Collapse patterns - What causes collapses
 * 3. Survival patterns - What allows worlds to survive
 */
class PatternDetector
{
    /**
     * Detect patterns across multiple sagas
     */
    public function detectAcrossSagas(array $sagaIds): array
    {
        $sagas = Saga::whereIn('id', $sagaIds)
            ->where('status', Saga::STATUS_COMPLETED)
            ->get();

        return [
            'archetype_patterns' => $this->detectArchetypePatterns($sagas),
            'collapse_patterns' => $this->detectCollapsePatterns($sagas),
            'divergence_patterns' => $this->detectDivergencePatterns($sagas),
        ];
    }

    /**
     * Detect archetype patterns across sagas
     */
    private function detectArchetypePatterns($sagas): array
    {
        $archetypeFrequency = [];

        foreach ($sagas as $saga) {
            $observations = $saga->observations()
                ->where('observation_type', SagaObservation::TYPE_ARCHETYPE_SHIFT)
                ->get();

            foreach ($observations as $obs) {
                $archetype = $obs->context['archetype'] ?? null;
                
                if ($archetype) {
                    if (!isset($archetypeFrequency[$archetype])) {
                        $archetypeFrequency[$archetype] = 0;
                    }
                    $archetypeFrequency[$archetype]++;
                }
            }
        }

        arsort($archetypeFrequency);

        return [
            'most_frequent' => $archetypeFrequency,
            'patterns' => $this->interpretArchetypeFrequency($archetypeFrequency, $sagas->count()),
        ];
    }

    /**
     * Interpret archetype frequency
     */
    private function interpretArchetypeFrequency(array $frequency, int $sagaCount): array
    {
        $patterns = [];

        foreach ($frequency as $archetype => $count) {
            $percentage = ($count / $sagaCount) * 100;

            if ($percentage > 75) {
                $patterns[] = "'{$archetype}' appears in most sagas (universal pattern)";
            } elseif ($percentage > 50) {
                $patterns[] = "'{$archetype}' is a common pattern";
            } elseif ($percentage > 25) {
                $patterns[] = "'{$archetype}' appears occasionally";
            }
        }

        return $patterns;
    }

    /**
     * Detect collapse patterns
     */
    private function detectCollapsePatterns($sagas): array
    {
        $collapseArchetypes = [];
        $totalCollapses = 0;

        foreach ($sagas as $saga) {
            $observations = $saga->observations()
                ->where('observation_type', SagaObservation::TYPE_PATTERN)
                ->where('observation', 'like', '%collapse%')
                ->get();

            $totalCollapses += $saga->getCollapseCount();

            foreach ($observations as $obs) {
                $archetype = $obs->context['archetype'] ?? null;
                
                if ($archetype) {
                    if (!isset($collapseArchetypes[$archetype])) {
                        $collapseArchetypes[$archetype] = 0;
                    }
                    $collapseArchetypes[$archetype]++;
                }
            }
        }

        arsort($collapseArchetypes);

        return [
            'total_collapses' => $totalCollapses,
            'collapse_triggers' => $collapseArchetypes,
            'avg_collapses_per_saga' => $sagas->count() > 0 ? $totalCollapses / $sagas->count() : 0,
        ];
    }

    /**
     * Detect divergence patterns
     */
    private function detectDivergencePatterns($sagas): array
    {
        $divergences = [];

        foreach ($sagas as $saga) {
            $observations = $saga->observations()
                ->where('observation_type', SagaObservation::TYPE_DIVERGENCE)
                ->get();

            foreach ($observations as $obs) {
                $divergences[] = [
                    'saga_id' => $saga->id,
                    'observation' => $obs->observation,
                    'context' => $obs->context,
                ];
            }
        }

        return [
            'count' => count($divergences),
            'examples' => array_slice($divergences, 0, 5),
        ];
    }

    /**
     * Detect pattern in single saga
     */
    public function detectInSaga(Saga $saga): array
    {
        return [
            'collapse_frequency' => $this->analyzeCollapseFrequency($saga),
            'archetype_dominance' => $this->analyzeArchetypeDominance($saga),
            'myth_recurrence' => $this->analyzeMythRecurrence($saga),
        ];
    }

    /**
     * Analyze collapse frequency within saga
     */
    private function analyzeCollapseFrequency(Saga $saga): array
    {
        $collapses = $saga->sagaWorlds()
            ->where('status', SagaWorld::STATUS_COLLAPSED)
            ->get();

        $sequences = $collapses->pluck('sequence')->toArray();

        return [
            'count' => $collapses->count(),
            'rate' => $saga->world_count > 0 ? $collapses->count() / $saga->world_count : 0,
            'sequences' => $sequences,
            'pattern' => $this->detectSequencePattern($sequences),
        ];
    }

    /**
     * Detect pattern in sequence numbers
     */
    private function detectSequencePattern(array $sequences): string
    {
        if (empty($sequences)) {
            return 'No collapses';
        }

        if (count($sequences) === 1) {
            return 'Single collapse';
        }

        // Check if sequential
        $sorted = $sequences;
        sort($sorted);
        $isSequential = true;

        for ($i = 1; $i < count($sorted); $i++) {
            if ($sorted[$i] !== $sorted[$i-1] + 1) {
                $isSequential = false;
                break;
            }
        }

        if ($isSequential) {
            return 'Sequential cascade';
        }

        return 'Scattered collapses';
    }

    /**
     * Analyze archetype dominance
     */
    private function analyzeArchetypeDominance(Saga $saga): array
    {
        // Implementation would analyze which archetypes were most dominant
        return [];
    }

    /**
     * Analyze myth recurrence
     */
    private function analyzeMythRecurrence(Saga $saga): array
    {
        // Implementation would analyze recurring myth patterns
        return [];
    }
}
