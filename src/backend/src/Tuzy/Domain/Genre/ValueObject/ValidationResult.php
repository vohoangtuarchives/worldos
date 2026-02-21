<?php

declare(strict_types=1);

namespace Tuzy\Domain\Genre\ValueObject;

final readonly class ValidationResult
{
    public function __construct(
        public bool $valid,
        public array $violations = [],
        public bool $repairable = false,
    ) {
    }

    public static function pass(): self
    {
        return new self(true);
    }

    public static function fail(array $violations, bool $repairable = true): self
    {
        return new self(false, $violations, $repairable);
    }
}
