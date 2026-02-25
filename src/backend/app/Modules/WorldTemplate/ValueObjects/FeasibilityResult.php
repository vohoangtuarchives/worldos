<?php

declare(strict_types=1);

namespace App\Modules\WorldTemplate\ValueObjects;

/**
 * Feasibility Result — outcome of F(θ) validation.
 *
 * Determines whether a LawVector can produce a viable Universe.
 */
final readonly class FeasibilityResult
{
    /**
     * @param bool $feasible Whether the universe is viable
     * @param array<string> $violations List of constraint violations
     * @param array<string, float> $scores Individual constraint scores
     */
    public function __construct(
        public bool $feasible,
        public array $violations = [],
        public array $scores = [],
    ) {
    }

    public static function pass(array $scores = []): self
    {
        return new self(feasible: true, violations: [], scores: $scores);
    }

    /**
     * @param array<string> $violations
     */
    public static function fail(array $violations, array $scores = []): self
    {
        return new self(feasible: false, violations: $violations, scores: $scores);
    }

    public function isPassing(): bool
    {
        return $this->feasible;
    }

    public function isFailing(): bool
    {
        return !$this->feasible;
    }
}
