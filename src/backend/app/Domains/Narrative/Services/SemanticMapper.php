<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Services;

/**
 * Layer 2: Map (situation key + velocity) to narrative meaning for renderer.
 */
class SemanticMapper
{
    /**
     * Maps situation key + velocity to a short narrative meaning label.
     */
    public function toNarrativeMeaning(string $situationKey, string $velocity): string
    {
        $k = $situationKey . ':' . $velocity;
        $meanings = [
            'inequality_high:rising_fast' => 'pre-revolution tension',
            'inequality_high:rising' => 'deepening stratification',
            'inequality_high:stable' => 'structural stratification',
            'inequality_high:falling' => 'redistribution in progress',
            'trauma_high:rising_fast' => 'collective shock',
            'trauma_high:stable' => 'unhealed scars',
            'elite_fractured:rising_fast' => 'elite civil war looming',
            'elite_fractured:stable' => 'fractured ruling class',
            'entropy_high:rising_fast' => 'rapid collapse of order',
            'entropy_high:stable' => 'entropy dominant',
            'pressure_critical:rising_fast' => 'system at breaking point',
            'pressure_critical:stable' => 'sustained critical pressure',
            'innovation_high:rising' => 'wave of change',
            'innovation_high:stable' => 'high innovation regime',
            'military_high_cohesion_low:stable' => 'war and division',
            'stagnation_risk:stable' => 'stagnation and brittleness',
            'resource_scarce:stable' => 'scarcity and survival',
            'resource_abundant:stable' => 'abundance with inequality',
            'order_high_entropy_low:stable' => 'rigid order',
            'neutral:stable' => 'balanced forces',
        ];
        return $meanings[$k] ?? $situationKey;
    }
}
