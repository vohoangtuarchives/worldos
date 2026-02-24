<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Myth\Mathematics;

final class MythCouplingMatrix
{
    /**
     * @param array<string, array<string, float>> $heroCoupling
     * @param array<string, array<string, float>> $universeCoupling
     */
    public function __construct(
        private readonly array $heroCoupling,
        private readonly array $universeCoupling,
    ) {
    }

    public function heroInfluence(string $mythDim, string $heroDim): float
    {
        return $this->heroCoupling[$mythDim][$heroDim] ?? 0.0;
    }

    public function universeInfluence(string $mythDim, string $universeDim): float
    {
        return $this->universeCoupling[$mythDim][$universeDim] ?? 0.0;
    }
}
