<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Saga\Services;

use WorldOS\Saga\Domain\Legacy\ValueObject\SagaEvaluationReport;
use WorldOS\Legacy\Application\Saga\Services\StabilityConstraint;

/**
 * Phase 4.1: Apply evaluation report to suggest next World blueprint (clamped, anti-oscillation).
 * Phase 4.2: Optional ConvergenceController for centroid pull and exploration decay.
 */
final class BlueprintMutationPlanner
{
    private const MAX_DELTA = 0.2;
    private const EXPLORATION_NOISE = 0.05;
    private const STABILITY_REPAIR_DELTA = 0.12;

    public function __construct(
        private readonly ?ConvergenceController $convergenceController = null,
        private readonly ?StabilityConstraint $stabilityConstraint = null,
    ) {
    }

    /**
     * Return legacy-shaped array for next World (preset/gene_vector hints). Does not create World.
     *
     * @return array{archetype_legacy?: array, mutation_bias?: array<string, float>, collapse_type?: string}
     */
    public function planFromReport(
        SagaEvaluationReport $report,
        ?int $sagaId = null,
        ?int $worldSequence = null
    ): array {
        $bias = $report->mutationSuggestions;
        foreach ($bias as $k => $v) {
            $bias[$k] = max(-self::MAX_DELTA, min(self::MAX_DELTA, (float) $v));
        }
        $bias['stability_bias'] = $report->stabilityScore * 0.1;
        $bias['resilience_bias'] = $report->resilienceIndex * 0.1;

        $objectiveVector = [
            'stability' => $bias['stability_bias'],
            'resilience' => $bias['resilience_bias'],
            'entropy_control' => $bias['order_bias'] ?? 0,
        ];
        if ($this->stabilityConstraint !== null && $this->stabilityConstraint->violated($objectiveVector)) {
            $bias['stability_bias'] = min(self::MAX_DELTA, $bias['stability_bias'] + self::STABILITY_REPAIR_DELTA);
            $bias['resilience_bias'] = min(self::MAX_DELTA, $bias['resilience_bias'] + self::STABILITY_REPAIR_DELTA);
        }

        if (
            $this->convergenceController !== null
            && $sagaId !== null
            && $worldSequence !== null
        ) {
            $explorationFactor = $this->convergenceController->explorationFactor($worldSequence);
            $centroid = $this->convergenceController->centroidForSaga($sagaId);
            if ($centroid !== null) {
                $currentObjective = [
                    'stability' => $bias['stability_bias'] ?? 0,
                    'resilience' => $bias['resilience_bias'] ?? 0,
                    'entropy_control' => $bias['order_bias'] ?? 0,
                ];
                $strength = 1.0 - $explorationFactor;
                $pulled = $this->convergenceController->pullTowardCentroid(
                    $currentObjective,
                    $centroid,
                    $strength
                );
                $bias['stability_bias'] = $pulled['stability'];
                $bias['resilience_bias'] = $pulled['resilience'];
                if (array_key_exists('order_bias', $bias) || isset($pulled['entropy_control'])) {
                    $bias['order_bias'] = $pulled['entropy_control'];
                }
            }
            foreach (array_keys($bias) as $k) {
                $noise = (mt_rand(-1000, 1000) / 1000.0) * self::EXPLORATION_NOISE * $explorationFactor;
                $bias[$k] = max(-self::MAX_DELTA, min(self::MAX_DELTA, $bias[$k] + $noise));
            }
        }

        return [
            'mutation_bias' => $bias,
            'collapse_type' => $report->collapseType,
        ];
    }
}
