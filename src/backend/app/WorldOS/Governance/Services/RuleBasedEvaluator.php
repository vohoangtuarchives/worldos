<?php

declare(strict_types=1);

namespace App\WorldOS\Governance\Services;

use App\WorldOS\Governance\Contracts\UniverseEvaluatorInterface;
use App\WorldOS\Governance\ValueObjects\EvaluationResult;
use App\WorldOS\Governance\ValueObjects\UniverseMetrics;

/**
 * Rule-Based Evaluator — deterministic governance decisions.
 *
 * Stub for future LLM-based evaluation. Uses fixed thresholds.
 *
 * Decision logic:
 *   1. collapse_risk ≥ 0.85 → ARCHIVE (save resources)
 *   2. ipScore ≥ 0.7 → FORK (preserve this interesting branch)
 *   3. stagnant → ARCHIVE (nothing happening)
 *   4. else → CONTINUE
 */
final class RuleBasedEvaluator implements UniverseEvaluatorInterface
{
    public function evaluate(UniverseMetrics $metrics): EvaluationResult
    {
        // Rule 1: Imminent collapse
        if ($metrics->collapseRisk >= 0.85) {
            return new EvaluationResult(
                recommendation: EvaluationResult::ARCHIVE,
                confidence: 0.9,
                reasoning: "Collapse risk ({$metrics->collapseRisk}) exceeds threshold. Archiving to preserve state.",
            );
        }

        // Rule 2: High IP potential — fork to preserve this branch
        if ($metrics->ipScore >= 0.7 && $metrics->ticksAnalyzed >= 5) {
            return new EvaluationResult(
                recommendation: EvaluationResult::FORK,
                confidence: 0.8,
                reasoning: "High IP score ({$metrics->ipScore}). Forking to preserve this timeline branch.",
                mutationSuggestion: $this->suggestMutation($metrics),
            );
        }

        // Rule 3: Stagnation
        if ($metrics->isStagnant() && $metrics->ticksAnalyzed >= 10) {
            return new EvaluationResult(
                recommendation: EvaluationResult::ARCHIVE,
                confidence: 0.7,
                reasoning: "Universe is stagnant. Low complexity and no trends detected.",
                mutationSuggestion: 'inject_entropy_burst',
            );
        }

        // Rule 4: Continue with optional pressure
        $mutation = null;
        if ($metrics->stabilityScore > 0.9 && $metrics->ticksAnalyzed >= 5) {
            $mutation = 'apply_selection_pressure'; // Too stable = boring
        }

        return new EvaluationResult(
            recommendation: EvaluationResult::CONTINUE,
            confidence: 0.6,
            reasoning: "Universe is evolving normally. Continuing observation.",
            mutationSuggestion: $mutation,
        );
    }

    private function suggestMutation(UniverseMetrics $metrics): ?string
    {
        if ($metrics->innovationTrend > 0.5) {
            return 'amplify_innovation';
        }
        if ($metrics->entropyTrend > 0.5) {
            return 'stabilize_entropy';
        }

        return null;
    }
}
