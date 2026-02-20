<?php

declare(strict_types=1);

namespace Tuzy\Domain\World\ValueObject;

final class WorldLawProfile
{
    public function __construct(
        private readonly float $beliefToRealityRatio,
        private readonly bool $mythEmergenceEnabled,
    ) {
    }

    public function getBeliefToRealityRatio(): float
    {
        return $this->beliefToRealityRatio;
    }

    public function isMythEmergenceEnabled(): bool
    {
        return $this->mythEmergenceEnabled;
    }

    public function equals(self $other): bool
    {
        return $this->beliefToRealityRatio === $other->beliefToRealityRatio
            && $this->mythEmergenceEnabled === $other->mythEmergenceEnabled;
    }
}
