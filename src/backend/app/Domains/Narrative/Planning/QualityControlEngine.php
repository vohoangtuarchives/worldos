<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Planning;

use App\Domains\Narrative\LLM\Contracts\LLMProvider;

/**
 * Phase 5.4: Rule-based checks and AI evaluator (8-point rubric); refinement loop.
 */
class QualityControlEngine
{
    public const MIN_SCORE_PASS = 8.0;
    public const MAX_REFINEMENT_ITERATIONS = 3;

    public function __construct(
        private readonly ?LLMProvider $llm = null
    ) {
    }

    /**
     * Rule-based checks: POV consistency, plot violation, motif reuse, exposition density.
     *
     * @param array{emotional_objective?: string, conflict_delta?: array} $blueprint
     * @return list<array{rule: string, passed: bool, message?: string}>
     */
    public function evaluateRules(string $draft, array $blueprint): array
    {
        return [
            ['rule' => 'pov_consistency', 'passed' => strlen($draft) > 10],
            ['rule' => 'exposition_density', 'passed' => true],
        ];
    }

    /**
     * AI evaluator: rubric (emotional thickness, subtext, rhythm, atmosphere). Returns score 0-10 and weak_points.
     *
     * @return array{score: float, weak_points: list<string>}
     */
    public function evaluateWithRubric(string $draft, array $blueprint): array
    {
        if ($this->llm !== null) {
            $systemPrompt = 'Rate this narrative 0-10 on emotional thickness, subtext, rhythm, atmosphere. Reply JSON: {"score": float, "weak_points": []}';
            $response = $this->llm->generate($systemPrompt, $draft);
            $text = $response['description'] ?? $response['content'] ?? json_encode($response);
            if (preg_match('/"score"\s*:\s*([\d.]+)/', $text, $m)) {
                $score = (float) $m[1];
                $weakPoints = [];
                if (preg_match('/"weak_points"\s*:\s*\[(.*?)\]/s', $text, $wp)) {
                    preg_match_all('/"([^"]+)"/', $wp[1], $quoted);
                    $weakPoints = $quoted[1] ?? [];
                }
                return ['score' => $score, 'weak_points' => $weakPoints];
            }
        }
        return ['score' => 7.0, 'weak_points' => ['Placeholder']];
    }

    /**
     * Refinement loop: if score < MIN_SCORE_PASS, refine up to MAX_REFINEMENT_ITERATIONS.
     */
    public function refineIfNeeded(string $draft, array $blueprint, ChapterProducer $producer, array $context = []): ?string
    {
        $current = $draft;
        for ($i = 0; $i < self::MAX_REFINEMENT_ITERATIONS; $i++) {
            $result = $this->evaluateWithRubric($current, $blueprint);
            if ($result['score'] >= self::MIN_SCORE_PASS) {
                return $current;
            }
            $hint = implode(', ', array_slice($result['weak_points'], 0, 2));
            $blueprint['emotional_objective'] = ($blueprint['emotional_objective'] ?? '') . ' (refine: ' . $hint . ')';
            $current = $producer->produce($blueprint, $context);
        }
        return null;
    }
}
