<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\AlertRule;
use Tuzy\Domain\Evolution\ValueObject\MetricsSnapshot;

/**
 * AlertEvaluationEngine â€” evaluates rules, manages cooldown, escalation, composites.
 *
 * Features:
 *   - Evaluate 15 rules against MetricsSnapshot
 *   - Cooldown: suppress repeat alerts for N epochs
 *   - Escalation: 3Ã— WARNING â†’ promote to CRITICAL
 *   - Composite: SSI_WARNING + COLLAPSE_IMMINENT â†’ SYSTEMIC_INSTABILITY
 *   - Auto-action dispatch (returns action codes, does not execute)
 */
class AlertEvaluationEngine
{
    /** @var array<string, int> code â†’ last fired epoch */
    private array $cooldowns = [];

    /** @var array<string, int> code â†’ consecutive fire count */
    private array $streaks = [];

    private const ESCALATION_THRESHOLD = 3; // 3 consecutive â†’ escalate

    /**
     * Evaluate all rules against a MetricsSnapshot.
     *
     * @return array{alerts: array, auto_actions: array, composites: array}
     */
    public function evaluate(MetricsSnapshot $metrics, int $currentEpoch, array $extraMetrics = []): array
    {
        $rules = AlertRule::catalog();
        $alerts = [];
        $autoActions = [];

        // Build metric map from MetricsSnapshot
        $metricMap = $this->buildMetricMap($metrics, $extraMetrics);

        foreach ($rules as $rule) {
            $value = $metricMap[$rule->metricSource] ?? null;
            if ($value === null) {
                continue;
            }

            $triggered = $rule->evaluate($value);

            if ($triggered) {
                // Cooldown check
                if ($this->isInCooldown($rule->code, $currentEpoch, $rule->cooldownEpochs)) {
                    continue;
                }

                // Track streak for escalation
                $this->streaks[$rule->code] = ($this->streaks[$rule->code] ?? 0) + 1;
                $this->cooldowns[$rule->code] = $currentEpoch;

                $severity = $rule->severity;

                // Escalation: 3 consecutive triggers â†’ promote severity
                if ($this->streaks[$rule->code] >= self::ESCALATION_THRESHOLD && $severity === 'WARNING') {
                    $severity = 'CRITICAL';
                }

                $alerts[] = [
                    'code' => $rule->code,
                    'domain' => $rule->domain,
                    'severity' => $severity,
                    'metric_value' => round($value, 4),
                    'threshold' => $rule->threshold,
                    'epoch' => $currentEpoch,
                    'description' => $rule->description,
                    'escalated' => $severity !== $rule->severity,
                    'streak' => $this->streaks[$rule->code],
                ];

                // Auto action
                if ($rule->autoAction !== null) {
                    $autoActions[] = [
                        'action' => $rule->autoAction,
                        'triggered_by' => $rule->code,
                        'epoch' => $currentEpoch,
                    ];
                }
            } else {
                // Reset streak on recovery
                $this->streaks[$rule->code] = 0;
            }
        }

        // Composite alerts
        $composites = $this->detectComposites($alerts, $currentEpoch);

        return [
            'alerts' => $alerts,
            'auto_actions' => $autoActions,
            'composites' => $composites,
        ];
    }

    /**
     * Detect composite (meta) alerts from co-occurring alerts.
     */
    private function detectComposites(array $alerts, int $epoch): array
    {
        $codes = array_column($alerts, 'code');
        $composites = [];

        // SYSTEMIC_INSTABILITY: SSI_WARNING + COLLAPSE_IMMINENT
        if (in_array('SSI_WARNING', $codes) && in_array('COLLAPSE_IMMINENT', $codes)) {
            $composites[] = [
                'code' => 'SYSTEMIC_INSTABILITY',
                'severity' => 'CRITICAL',
                'components' => ['SSI_WARNING', 'COLLAPSE_IMMINENT'],
                'epoch' => $epoch,
                'description' => 'Multiple instability indicators co-occurring â€” systemic crisis',
            ];
        }

        // Or SSI_CRITICAL + COLLAPSE_IMMINENT
        if (in_array('SSI_CRITICAL', $codes) && in_array('COLLAPSE_IMMINENT', $codes)) {
            $composites[] = [
                'code' => 'SYSTEMIC_INSTABILITY',
                'severity' => 'CRITICAL',
                'components' => ['SSI_CRITICAL', 'COLLAPSE_IMMINENT'],
                'epoch' => $epoch,
                'description' => 'Critical instability â€” immediate intervention required',
            ];
        }

        // POWER_CRISIS: MONOPOLY_RISK + POLARIZATION_HIGH
        if (in_array('MONOPOLY_RISK', $codes) && in_array('POLARIZATION_HIGH', $codes)) {
            $composites[] = [
                'code' => 'POWER_CRISIS',
                'severity' => 'HIGH',
                'components' => ['MONOPOLY_RISK', 'POLARIZATION_HIGH'],
                'epoch' => $epoch,
                'description' => 'Power concentration with social polarization',
            ];
        }

        // MEMORY_CRISIS: HISTORICAL_LOCK + GCMF_OVERBIAS
        if (in_array('HISTORICAL_LOCK', $codes) && in_array('GCMF_OVERBIAS', $codes)) {
            $composites[] = [
                'code' => 'MEMORY_CRISIS',
                'severity' => 'HIGH',
                'components' => ['HISTORICAL_LOCK', 'GCMF_OVERBIAS'],
                'epoch' => $epoch,
                'description' => 'Historical bias compounded by collective mood dominance',
            ];
        }

        return $composites;
    }

    /**
     * Check if an alert is in cooldown period.
     */
    private function isInCooldown(string $code, int $currentEpoch, int $cooldownEpochs): bool
    {
        if (!isset($this->cooldowns[$code])) {
            return false;
        }

        return ($currentEpoch - $this->cooldowns[$code]) < $cooldownEpochs;
    }

    /**
     * Build a flat metric map from MetricsSnapshot + extra metrics.
     */
    private function buildMetricMap(MetricsSnapshot $metrics, array $extra): array
    {
        return array_merge([
            'ssi' => $metrics->spectralStabilityIndex,
            'cf' => $metrics->collapseFrequency,
            'stability_margin' => $metrics->stabilityMargin,
            'di' => $metrics->diversityIndex,
            'agr' => $metrics->adaptationGainRate,
            'res' => $metrics->rebirthEffectiveness,
            'icr' => $metrics->influenceConcentration,
            'vps' => $metrics->votingPowerSkew,
            'api' => $metrics->alliancePolarization,
            'hbr' => $metrics->historicalBiasRatio,
            'cm' => $metrics->collectiveMomentum,
            'mde' => $metrics->memoryDecayEffectiveness,
            'epi' => $metrics->emergencePressure,
            'atr' => $metrics->archetypeTurnoverRate,
            'par' => $metrics->proposalAcceptanceRatio,
            'gl' => $metrics->governanceLatency,
            'hii' => $metrics->humanInterventionIndex,
            'chs' => $metrics->civilizationalHealthScore,
        ], $extra);
    }

    // --- State management for serialization ---

    public function toArray(): array
    {
        return [
            'cooldowns' => $this->cooldowns,
            'streaks' => $this->streaks,
        ];
    }

    public static function fromArray(array $data): self
    {
        $engine = new self();
        $engine->cooldowns = $data['cooldowns'] ?? [];
        $engine->streaks = $data['streaks'] ?? [];
        return $engine;
    }
}



