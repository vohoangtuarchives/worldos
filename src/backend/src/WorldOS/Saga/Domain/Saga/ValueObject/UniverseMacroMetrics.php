<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Saga\ValueObject;

final class UniverseMacroMetrics
{
    public function __construct(
        public readonly float $entropyMean,
        public readonly float $entropyVariance,
        public readonly float $entropyGradient,
        public readonly float $entropyAcceleration,
        public readonly float $tensionOscillation,
        public readonly float $stabilityIndex,
        public readonly float $turbulenceMeanWindow,
    ) {
    }
}
