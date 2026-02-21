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

    public function getStatus(): string
    {
        return $this->thresholdStatus['current'] ?? 'unknown';
    }

    public function toArray(): array
    {
        return [
            'legitimacy' => $this->legitimacy,
            'status' => $this->getStatus(),
            'components' => $this->components,
            'threshold_status' => $this->thresholdStatus,
        ];
    }
}
