<?php

namespace Tuzy\Domain\Material\ValueObject;

final readonly class SurvivalProbability
{
    private function __construct(
        private float $value
    ) {
        if ($value < 0.0 || $value > 1.0) {
            throw new \InvalidArgumentException('Survival probability must be between 0 and 1');
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function value(): float
    {
        return $this->value;
    }
}
