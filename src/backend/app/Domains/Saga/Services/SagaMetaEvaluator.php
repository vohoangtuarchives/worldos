<?php

declare(strict_types=1);

namespace App\Domains\Saga\Services;

use Tuzy\Domain\Saga\ValueObject\SagaEvaluationInput;
use Tuzy\Domain\Saga\ValueObject\SagaEvaluationReport;
use Tuzy\Domain\Saga\ValueObject\CollapseProfile;


/**
 * Phase 5: Single entry for evaluation. Layer 1 = CivilizationScorer (deterministic); Layer 2 = AI stub (sau mở rộng LLM).
 */
final class SagaMetaEvaluator
{
    public function __construct(
        private readonly CivilizationScorer $civilizationScorer
    ) {
    }

    /**
     * Evaluate collapse outcome and return report for next World mutation.
     * Layer 1: rule-based score; Layer 2: stub (return as-is; later: LLM suggestions).
     */
    public function evaluate(string $cause, array $finalState = []): SagaEvaluationReport
    {
        $report = $this->civilizationScorer->scoreFromCollapse($cause, $finalState);
        return $this->layer2Enhance($cause, $finalState, $report);
    }

    /**
     * Layer 2 stub: optional AI enhancement. For now returns report unchanged.
     */
    private function layer2Enhance(string $cause, array $finalState, SagaEvaluationReport $report): SagaEvaluationReport
    {
        $profile = CollapseProfile::fromCauseAndState($cause, $finalState);
        $entropy = (float) ($finalState['entropy'] ?? 0.5);
        // Structured input for future Layer 2 (LLM): archetype, mutation direction.
        new SagaEvaluationInput(
            $profile,
            $report->stabilityScore,
            $report->resilienceIndex,
            1.0 - $entropy,
            $finalState
        );
        return $report;
    }
}
