<?php

declare(strict_types=1);

namespace App\Domains\Runtime\Evaluation;

use App\Domains\Narrative\LLM\Contracts\LLMProvider;
use Illuminate\Support\Facades\Log;

/**
 * WorldOS v3: LLM-powered universe evaluator.
 *
 * Sends universe metrics to an LLM for narrative evaluation. Falls back to StubUniverseEvaluator
 * on timeout or parse failure. Enable via WORLDOS_EVALUATOR_DRIVER=llm in .env.
 *
 * Log pipeline: metrics → LLM prompt → raw response → parsed decision → EvaluationResult.
 */
class LLMUniverseEvaluator implements UniverseEvaluatorInterface
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
You are an AI narrative evaluator for WorldOS simulation universes. Given universe metrics, evaluate:
1. IP Score (0-1): How valuable is this universe as intellectual property?
2. Narrative Potential (0-1): How compelling are the stories this universe could generate?
3. Collapse Probability (0-1): How likely is this universe to collapse?
4. Recommendation: "continue", "fork", or "archive"
5. Mutation Suggestion (optional): If you recommend pressure, specify type (military|resource|ideology|tech) and intensity (0-1).

Respond ONLY in JSON format:
{"ip_score": 0.7, "narrative_potential": 0.8, "collapse_probability": 0.2, "recommendation": "continue", "mutation": null}
or with mutation:
{"ip_score": 0.7, "narrative_potential": 0.8, "collapse_probability": 0.2, "recommendation": "fork", "mutation": {"type": "military", "intensity": 0.5}}
PROMPT;

    public function __construct(
        private readonly LLMProvider $llm,
        private readonly StubUniverseEvaluator $fallback
    ) {
    }

    public function evaluate(UniverseMetrics $metrics): EvaluationResult
    {
        try {
            return $this->evaluateViaLLM($metrics);
        } catch (\Throwable $e) {
            Log::warning('LLMUniverseEvaluator: fallback to stub', [
                'error' => $e->getMessage(),
                'metrics' => $metrics->toArray(),
            ]);
            return $this->fallback->evaluate($metrics);
        }
    }

    private function evaluateViaLLM(UniverseMetrics $metrics): EvaluationResult
    {
        $userPrompt = sprintf(
            "Universe Metrics:\n%s",
            json_encode($metrics->toArray(), JSON_PRETTY_PRINT)
        );

        Log::info('LLMUniverseEvaluator: sending metrics to LLM', [
            'metrics' => $metrics->toArray(),
        ]);

        $response = $this->llm->generate(self::SYSTEM_PROMPT, $userPrompt);

        Log::info('LLMUniverseEvaluator: raw LLM response', [
            'response' => $response,
        ]);

        return $this->parseResponse($response, $metrics);
    }

    private function parseResponse(array $response, UniverseMetrics $metrics): EvaluationResult
    {
        $ipScore = $this->clamp01((float) ($response['ip_score'] ?? 0.5));
        $narrativePotential = $this->clamp01((float) ($response['narrative_potential'] ?? 0.5));
        $collapseProbability = $this->clamp01((float) ($response['collapse_probability'] ?? $metrics->collapseRisk));

        $recommendation = $response['recommendation'] ?? EvaluationResult::RECOMMENDATION_CONTINUE;
        if (!in_array($recommendation, [
            EvaluationResult::RECOMMENDATION_CONTINUE,
            EvaluationResult::RECOMMENDATION_FORK,
            EvaluationResult::RECOMMENDATION_ARCHIVE,
        ], true)) {
            $recommendation = EvaluationResult::RECOMMENDATION_CONTINUE;
        }

        $mutationSuggestion = null;
        if (isset($response['mutation']) && is_array($response['mutation'])) {
            $type = $response['mutation']['type'] ?? null;
            $intensity = (float) ($response['mutation']['intensity'] ?? 0.5);
            if (in_array($type, ['military', 'resource', 'ideology', 'tech'], true)) {
                $mutationSuggestion = new MutationSuggestion($type, $this->clamp01($intensity));
            }
        }

        $result = new EvaluationResult(
            ipScore: $ipScore,
            narrativePotential: $narrativePotential,
            collapseProbability: $collapseProbability,
            recommendation: $recommendation,
            mutationSuggestion: $mutationSuggestion,
        );

        Log::info('LLMUniverseEvaluator: decision', [
            'ip_score' => $result->ipScore,
            'narrative_potential' => $result->narrativePotential,
            'collapse_probability' => $result->collapseProbability,
            'recommendation' => $result->recommendation,
            'mutation' => $mutationSuggestion ? ['type' => $mutationSuggestion->type, 'intensity' => $mutationSuggestion->intensity] : null,
        ]);

        return $result;
    }

    private function clamp01(float $v): float
    {
        return max(0.0, min(1.0, $v));
    }
}
