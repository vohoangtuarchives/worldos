<?php

namespace App\Domains\SocialSimulation;

/**
 * Legitimacy Data Transfer Object
 */
class LegitimacyDTO
{
    public function __construct(
        public readonly float $legitimacy,
        public readonly array $components,
        public readonly array $threshold_status
    ) {}

    /**
     * Check if legitimacy is in collapse range
     */
    public function isCollapse(): bool
    {
        return $this->legitimacy <= 0.2;
    }

    /**
     * Check if legitimacy is stable
     */
    public function isStable(): bool
    {
        return $this->legitimacy > 0.7;
    }

    /**
     * Get current status
     */
    public function getStatus(): string
    {
        return $this->threshold_status['current'];
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'legitimacy' => $this->legitimacy,
            'status' => $this->getStatus(),
            'components' => $this->components,
            'threshold_status' => $this->threshold_status,
        ];
    }
}
