<?php

declare(strict_types=1);

namespace Tuzy\Domain\Historian\ValueObject;

/**
 * Scope for historian queries (world, saga, pattern).
 * Domain-only.
 */
final readonly class HistorianScope
{
    public function __construct(
        public string $worldId,
        public ?string $sagaId = null,
        public ?string $patternType = null,
    ) {
    }
}
