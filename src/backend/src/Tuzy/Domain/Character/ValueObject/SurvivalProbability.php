<?php

declare(strict_types=1);

namespace Tuzy\Domain\Character\ValueObject;

use InvalidArgumentException;

readonly class SurvivalProbability
{
    private function __construct(
        private float $value
    ) {
        if ($value < 0.0 || $value > 1.0) {
            throw new InvalidArgumentException('Survival probability must be between 0 and 1');
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public static function certain(): self
    {
        return new self(1.0);
    }

    public static function impossible(): self
    {
        return new self(0.0);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function isCertain(): bool
    {
        return $this->value === 1.0;
    }

    public function isImpossible(): bool
    {
        return $this->value === 0.0;
    }

    public function isHighRisk(): bool
    {
        return $this->value < 0.3;
    }

    public function isModerateRisk(): bool
    {
        return $this->value >= 0.3 && $this->value < 0.7;
    }

    public function isLowRisk(): bool
    {
        return $this->value >= 0.7;
    }

    public function adjust(float $delta): self
    {
        return new self(max(0.0, min(1.0, $this->value + $delta)));
    }

    public function multiply(float $factor): self
    {
        return new self(max(0.0, min(1.0, $this->value * $factor)));
    }

    public function compare(self $other): int
    {
        return $this->value <=> $other->value;
    }
}
