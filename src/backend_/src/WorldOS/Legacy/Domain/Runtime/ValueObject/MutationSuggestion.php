<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Runtime\ValueObject;

/**
 * WorldOS v3 Phase 3: Structured suggestion from AI; Kernel validates before applyPressure.
 */
final readonly class MutationSuggestion
{
    public function __construct(
        public string $type,   // e.g. military, resource, ideology, tech
        public float $intensity,
    ) {
    }
}
