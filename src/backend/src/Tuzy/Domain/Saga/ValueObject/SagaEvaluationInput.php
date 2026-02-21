<?php

declare(strict_types=1);

namespace Tuzy\Domain\Saga\ValueObject;

/**
 * Structured input for SagaMetaEvaluator (Layer 2 AI — optional).
 */
readonly class SagaEvaluationInput
{
    public function __construct(
        public CollapseProfile $collapseProfile,
        public float $stabilityScore,
        public float $resilienceIndex,
        public float $entropyControl,
        public array $finalState = [],
    ) {
    }
}
