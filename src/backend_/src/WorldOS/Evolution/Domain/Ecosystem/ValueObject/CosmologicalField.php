<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Ecosystem\ValueObject;

final class CosmologicalField
{
    public function __construct(
        public readonly float $entropyBackground,
        public readonly float $turbulencePressure,
        public readonly float $mythicResonance,
        public readonly float $spectralDrift,
    ) {
    }
}
