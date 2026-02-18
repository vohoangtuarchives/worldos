<?php

declare(strict_types=1);

namespace App\Domains\Saga\DTO;

use App\Domains\Saga\ValueObjects\CollapseProfile;

/**
 * Phase 5: Structured input for SagaMetaEvaluator (Layer 2 AI — optional).
 */
final readonly class SagaEvaluationInput
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
