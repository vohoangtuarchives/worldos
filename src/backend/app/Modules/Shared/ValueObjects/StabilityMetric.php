<?php

declare(strict_types=1);

namespace App\Modules\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Stability Metric — σ(U) ∈ [0, 1].
 *
 * Heuristic measure of universe stability:
 *   σ < 0.1 → Collapse imminent
 *   0.1 ≤ σ < 0.3 → Unstable / crisis
 *   0.3 ≤ σ < 0.7 → Normal oscillation
 *   σ ≥ 0.7 → Stable high-order civilization
 *
 * Immutable Value Object.
 */
final readonly class StabilityMetric
{
    public const float COLLAPSE_THRESHOLD = 0.1;
    public const float CRISIS_THRESHOLD = 0.3;
    public const float STABLE_THRESHOLD = 0.7;

    public function __construct(
        public float $value,
    ) {
        if ($value < 0.0 || $value > 1.0) {
            throw new InvalidArgumentException(
                "Stability metric must be in [0.0, 1.0], got: {$value}"
            );
        }
    }

    public function isCollapsing(): bool
    {
        return $this->value < self::COLLAPSE_THRESHOLD;
    }

    public function isCrisis(): bool
    {
        return $this->value >= self::COLLAPSE_THRESHOLD
            && $this->value < self::CRISIS_THRESHOLD;
    }

    public function isNormal(): bool
    {
        return $this->value >= self::CRISIS_THRESHOLD
            && $this->value < self::STABLE_THRESHOLD;
    }

    public function isStable(): bool
    {
        return $this->value >= self::STABLE_THRESHOLD;
    }

    public function equals(self $other): bool
    {
        return abs($this->value - $other->value) < PHP_FLOAT_EPSILON;
    }
}
