<?php

declare(strict_types=1);

namespace Tuzy\Domain\Runtime\ValueObject;

/** WorldOS v3 Phase 3: Output of UniverseEvaluator for DecisionEngine. */
final readonly class EvaluationResult
{
    public const RECOMMENDATION_CONTINUE = 'continue';
    public const RECOMMENDATION_FORK = 'fork';
    public const RECOMMENDATION_ARCHIVE = 'archive';

    public function __construct(
        public float $ipScore,
        public float $narrativePotential,
        public float $collapseProbability,
        public string $recommendation,
        public ?MutationSuggestion $mutationSuggestion = null,
    ) {
    }
}
