<?php

declare(strict_types=1);

namespace Tuzy\Domain\World\ValueObject;

readonly class EntropyScore
{
    public function __construct(
        private float $value,
    ) {
    }

    public function value(): float
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
