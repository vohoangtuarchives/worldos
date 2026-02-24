<?php

declare(strict_types=1);

namespace App\WorldOS\CivilizationMemory\ValueObjects;

use InvalidArgumentException;

/**
 * Scar Weight — severity of a historical consequence (1-10).
 */
final readonly class ScarWeight
{
    public function __construct(
        public int $value,
    ) {
        if ($value < 1 || $value > 10) {
            throw new InvalidArgumentException(
                "ScarWeight must be between 1 and 10, got: {$value}"
            );
        }
    }

    public function isHeavy(): bool
    {
        return $this->value >= 7;
    }

    public function isMedium(): bool
    {
        return $this->value >= 4 && $this->value < 7;
    }

    public function isLight(): bool
    {
        return $this->value < 4;
    }
}
