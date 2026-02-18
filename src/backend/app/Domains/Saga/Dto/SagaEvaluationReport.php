<?php

declare(strict_types=1);

namespace App\Domains\Saga\DTO;

/**
 * Phase 4: Output of SagaMetaEvaluator / CivilizationScorer for next World mutation.
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
