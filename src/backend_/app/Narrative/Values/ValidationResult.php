<?php

namespace App\Narrative\Values;

class ValidationResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly array $violations = [],
        public readonly bool $repairable = false
    ) {}

    public static function pass(): self
    {
        return new self(true);
    }

    public static function fail(array $violations, bool $repairable = true): self
    {
        return new self(false, $violations, $repairable);
    }
}
