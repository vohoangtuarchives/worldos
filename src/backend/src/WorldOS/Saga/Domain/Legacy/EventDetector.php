<?php

namespace WorldOS\Saga\Domain\Legacy;

use WorldOS\Legacy\Application\Material\State\WorldState;

/**
 * EventDetector - Detect significant events from state changes
 * 
 * Event Types:
 * - Threshold: State crosses critical value
 * - Shock: Sudden large change
 * - Trend: Sustained change over time
 */
class EventDetector
{
    /**
     * Detect events from state changes.
     * 
     * @param array $stateChanges From SagaInterpreter
     * @param WorldState $currentState
     * @return array Detected events
     */
    public function detect(array $stateChanges, WorldState $currentState): array
    {
        $events = [];

        // Threshold events
        $events = array_merge($events, $this->detectThresholdEvents($currentState));

        // Shock events
        $events = array_merge($events, $this->detectShockEvents($stateChanges));

        return $events;
    }

    /**
     * Detect threshold events (state crosses critical value).
     */
    private function detectThresholdEvents(WorldState $state): array
    {
        $events = [];

        // Famine threshold
        if ($state->core->subsistenceBase < 0.3) {
            $events[] = [
                'type' => 'famine',
                'category' => 'economic',
                'severity' => round(1.0 - $state->core->subsistenceBase, 2),
                'narrative_template' => 'famine_crisis',
                'data' => [
                    'subsistence_base' => $state->core->subsistenceBase,
                ],
            ];
        }

        // Collapse proximity
        if ($state->meta->collapseProximity > 0.7) {
            $events[] = [
                'type' => 'collapse_warning',
                'category' => 'systemic',
                'severity' => round($state->meta->collapseProximity, 2),
                'narrative_template' => 'system_instability',
                'data' => [
                    'collapse_proximity' => $state->meta->collapseProximity,
                ],
            ];
        }

        // High inequality
        if ($state->structural->inequality > 0.8) {
            $events[] = [
                'type' => 'inequality_crisis',
                'category' => 'social',
                'severity' => round($state->structural->inequality, 2),
                'narrative_template' => 'social_tension',
                'data' => [
                    'inequality' => $state->structural->inequality,
                ],
            ];
        }

        // High trauma
        if ($state->memory->traumaDensity > 0.7) {
            $events[] = [
                'type' => 'trauma_accumulation',
                'category' => 'memory',
                'severity' => round($state->memory->traumaDensity, 2),
                'narrative_template' => 'collective_trauma',
                'data' => [
                    'trauma_density' => $state->memory->traumaDensity,
                ],
            ];
        }

        // External threat
        if ($state->interaction->externalThreat > 0.6) {
            $events[] = [
                'type' => 'external_threat',
                'category' => 'interaction',
                'severity' => round($state->interaction->externalThreat, 2),
                'narrative_template' => 'foreign_pressure',
                'data' => [
                    'external_threat' => $state->interaction->externalThreat,
                ],
            ];
        }

        return $events;
    }

    /**
     * Detect shock events (sudden large changes).
     */
    private function detectShockEvents(array $changes): array
    {
        $events = [];

        $this->detectShocksRecursive($changes, '', $events);

        return $events;
    }

    /**
     * Recursively detect shocks in nested changes.
     */
    private function detectShocksRecursive(array $changes, string $prefix, array &$events): void
    {
        foreach ($changes as $field => $change) {
            $fullField = $prefix ? "{$prefix}.{$field}" : $field;

            if (isset($change['delta']) && abs($change['delta']) > 0.25) {
                $events[] = [
                    'type' => 'shock',
                    'category' => 'sudden_change',
                    'severity' => round(abs($change['delta']), 2),
                    'narrative_template' => 'sudden_change',
                    'data' => [
                        'field' => $fullField,
                        'magnitude' => abs($change['delta']),
                        'direction' => $change['delta'] > 0 ? 'increase' : 'decrease',
                        'from' => $change['from'],
                        'to' => $change['to'],
                    ],
                ];
            } elseif (is_array($change) && !isset($change['delta'])) {
                $this->detectShocksRecursive($change, $fullField, $events);
            }
        }
    }

    /**
     * Calculate event severity (0.0 - 1.0).
     */
    public function calculateSeverity(array $event): float
    {
        return $event['severity'] ?? 0.5;
    }
}
