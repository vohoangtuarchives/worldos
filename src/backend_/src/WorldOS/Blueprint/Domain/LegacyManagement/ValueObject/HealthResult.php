<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\LegacyManagement\ValueObject;

use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldHealthStatus;

/**
 * Result of a world health calculation: status and optional violations.
 */
readonly class HealthResult
{
    /** @param list<array{code: string, message: string}> $violations */
    public function __construct(
        public WorldHealthStatus $status,
        public array $violations = [],
    ) {
    }

    public static function stable(): self
    {
        return new self(WorldHealthStatus::STABLE);
    }
}
