<?php

namespace App\Domains\Runtime\Evaluation;

use WorldOS\Legacy\Domain\Runtime\ValueObject\EvaluationResult;
use WorldOS\Legacy\Domain\Runtime\ValueObject\UniverseMetrics;

/**
 * WorldOS v3 Phase 3: Heuristic-only evaluator (no LLM). Guardrail: high entropy → archive.
 */
class StubUniverseEvaluator implements UniverseEvaluatorInterface
{
    private const ENTROPY_DISCARD_THRESHOLD = 0.92;

    public function evaluate(UniverseMetrics $metrics): EvaluationResult
    {
        $recommendation = EvaluationResult::RECOMMENDATION_CONTINUE;
        if ($metrics->entropyTrend >= self::ENTROPY_DISCARD_THRESHOLD || $metrics->collapseRisk >= 0.9) {
            $recommendation = EvaluationResult::RECOMMENDATION_ARCHIVE;
        } elseif ($metrics->stabilityScore > 0.7 && $metrics->noveltyIndex > 0.5) {
            $recommendation = EvaluationResult::RECOMMENDATION_FORK;
        }

        $ipScore = $metrics->stabilityScore * 0.4 + (1.0 - $metrics->collapseRisk) * 0.4 + $metrics->noveltyIndex * 0.2;
        $narrativePotential = $metrics->conflictDensity * 0.5 + $metrics->complexityIndex * 0.5;

        return new EvaluationResult(
            ipScore: max(0, min(1, $ipScore)),
            narrativePotential: max(0, min(1, $narrativePotential)),
            collapseProbability: $metrics->collapseRisk,
            recommendation: $recommendation,
            mutationSuggestion: null,
        );
    }
}
