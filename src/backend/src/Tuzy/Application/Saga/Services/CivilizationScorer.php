<?php

declare(strict_types=1);

namespace Tuzy\Application\Saga\Services;

use Tuzy\Domain\Saga\ValueObject\SagaEvaluationReport;

/**
 * Phase 4.1: Rule-based civilization scoring from collapse/state (no AI).
 */
final class CivilizationScorer
{
    public function scoreFromCollapse(string $cause, array $finalState = []): SagaEvaluationReport
    {
        $entropy = (float) ($finalState['entropy'] ?? 0.5);
        $order = (float) ($finalState['order'] ?? 0.5);
        $collapseType = $this->inferCollapseType($cause, $finalState);
        $stabilityScore = max(0, 1.0 - $entropy);
        $resilienceIndex = $order * 0.5 + (1.0 - $entropy) * 0.5;

        $mutationSuggestions = [];
        if (str_contains(strtolower($cause), 'inequality') || $collapseType === 'inequality_revolt') {
            $mutationSuggestions['redistribution_bias'] = 0.2;
        }
        if (str_contains(strtolower($cause), 'entropy') || $collapseType === 'entropy_overload') {
            $mutationSuggestions['order_bias'] = 0.15;
        }

        return new SagaEvaluationReport($stabilityScore, $resilienceIndex, $collapseType, $mutationSuggestions);
    }

    private function inferCollapseType(string $cause, array $finalState): string
    {
        if (str_contains(strtolower($cause), 'structural') || str_contains(strtolower($cause), 'fracture')) {
            return 'structural_fracture';
        }
        if (str_contains(strtolower($cause), 'entropy') || (($finalState['entropy'] ?? 0) > 0.9)) {
            return 'entropy_overload';
        }
        if (($finalState['inequality'] ?? 0) > 0.7) {
            return 'inequality_revolt';
        }
        return 'unknown';
    }
}
