<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\ValueObject;

final readonly class TransitionResult
{
    public function __construct(
        public bool $allowed,
        public array $warnings = [],
        public array $requiredMigrations = [],
    ) {
    }
}
