<?php

namespace App\Narrative\Constraints;

final class ConstraintResult
{
    public function __construct(
        public readonly bool $pass,
        public readonly ?string $reason = null
    ) {}

    public static function pass(): self
    {
        return new self(true);
    }

    public static function fail(string $reason): self
    {
        return new self(false, $reason);
    }
}
