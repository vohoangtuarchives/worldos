<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Legacy\ValueObject;

/**
 * Output of SagaMetaEvaluator / CivilizationScorer for next World mutation.
 */
final readonly class SagaEvaluationReport
{
    public function __construct(
        public float $stabilityScore,
        public float $resilienceIndex,
        public string $collapseType,
        public array $mutationSuggestions,
    ) {
    }
}
