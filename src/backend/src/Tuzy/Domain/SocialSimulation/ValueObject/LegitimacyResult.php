<?php

declare(strict_types=1);

namespace Tuzy\Domain\SocialSimulation\ValueObject;

/**
 * Result of legitimacy calculation (immutable).
 * Domain-only.
 */
final readonly class LegitimacyResult
{
    public function __construct(
        public float $legitimacy,
        public array $components = [],
        public array $thresholdStatus = [],
    ) {
    }

    public function isCollapse(): bool
    {
        return $this->legitimacy <= 0.2;
    }

    public function isStable(): bool
    {
        return $this->legitimacy > 0.7;
    }
}
