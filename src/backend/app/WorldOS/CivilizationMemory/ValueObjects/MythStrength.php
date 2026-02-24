<?php

declare(strict_types=1);

namespace App\WorldOS\CivilizationMemory\ValueObjects;

use InvalidArgumentException;

/**
 * Myth Strength — tracks the emergent power of a myth.
 *
 * From docs §8.4:
 *   Level 1 (0.2–0.4) → Level 2 (0.4–0.7) → Level 3 (≥0.7)
 *   No level skipping allowed.
 *
 * MythScore = Impact×0.35 + Irreversibility×0.30 + Compression×0.20 + Recurrence×0.15
 */
final readonly class MythStrength
{
    public function __construct(
        public float $value,
    ) {
        if ($value < 0.0 || $value > 1.0) {
            throw new InvalidArgumentException(
                "MythStrength must be between 0.0 and 1.0, got: {$value}"
            );
        }
    }

    public function getLevel(): int
    {
        if ($this->value >= 0.7) {
            return 3;
        }
        if ($this->value >= 0.4) {
            return 2;
        }
        if ($this->value >= 0.2) {
            return 1;
        }

        return 0; // Not yet a myth
    }

    /**
     * Calculate MythScore from component axes.
     *
     * @param float $impact          Scope, power structure, knowledge (0-1)
     * @param float $irreversibility Can it be undone? (0-1)
     * @param float $compression     Narrative compression — easy to symbolize? (0-1)
     * @param float $recurrence      Likelihood of recurrence in other worlds (0-1)
     */
    public static function calculateScore(
        float $impact,
        float $irreversibility,
        float $compression,
        float $recurrence,
    ): self {
        $score = $impact * 0.35
            + $irreversibility * 0.30
            + $compression * 0.20
            + $recurrence * 0.15;

        return new self(min(1.0, max(0.0, $score)));
    }

    /**
     * Apply decay to myth strength.
     */
    public function decay(float $rate): self
    {
        return new self(max(0.0, $this->value - $rate));
    }

    /**
     * Grow myth strength (from shared belief).
     * Cannot skip levels — capped at next level boundary.
     */
    public function grow(float $amount): self
    {
        $currentLevel = $this->getLevel();
        $newValue = $this->value + $amount;

        // Cap at next level boundary to prevent skipping
        $maxForNextLevel = match ($currentLevel) {
            0 => 0.4,   // Can grow into Level 1
            1 => 0.7,   // Can grow into Level 2
            2 => 1.0,   // Can grow into Level 3
            default => 1.0,
        };

        return new self(min($maxForNextLevel, max(0.0, $newValue)));
    }
}
