<?php

declare(strict_types=1);

namespace App\Domains\Runtime\Evaluation;

/** WorldOS v3 Phase 3: Output of UniverseEvaluator for DecisionEngine. */
final class EvaluationResult
{
    public const RECOMMENDATION_CONTINUE = 'continue';
    public const RECOMMENDATION_FORK = 'fork';
    public const RECOMMENDATION_ARCHIVE = 'archive';

    public function __construct(
        public readonly float $ipScore,
        public readonly float $narrativePotential,
        public readonly float $collapseProbability,
        public readonly string $recommendation, // fork | continue | archive
        public readonly ?MutationSuggestion $mutationSuggestion = null,
    ) {
    }
}
