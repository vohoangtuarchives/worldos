<?php

declare(strict_types=1);

namespace App\Domains\Runtime\Evaluation;

/**
 * WorldOS v3 Phase 3: Structured suggestion from AI; Kernel validates before applyPressure.
 */
final class MutationSuggestion
{
    public function __construct(
        public readonly string $type,   // e.g. military, resource, ideology, tech
        public readonly float $intensity,
    ) {
    }
}
