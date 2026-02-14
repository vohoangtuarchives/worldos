<?php

namespace App\Domains\World\ValueObjects;

class EntropyScore
{
    public function __construct(
        private float $value
    ) {}

    public function value(): float
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
