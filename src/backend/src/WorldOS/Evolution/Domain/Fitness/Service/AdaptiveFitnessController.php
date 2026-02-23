<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Fitness\Service;

final class FitnessWeights
{
    public function __construct(
        public readonly float $complexity,
        public readonly float $stability,
        public readonly float $regulation,
    ) {
    }
}

final class AdaptiveFitnessController
{
    /**
     * Determines fitness weights based on the volatility of the macro ecosystem.
     * Prevents system collapse by penalizing complexity during high volatility.
     */
    public function adjustWeights(float $ecosystemVolatility): FitnessWeights
    {
        $complexity = 0.6;
        $stability = 0.2;
        $regulation = 0.2;

        if ($ecosystemVolatility > 0.7) {
            $complexity -= 0.15;
            $stability += 0.1;
        }

        if ($ecosystemVolatility < 0.3) {
            $complexity += 0.1;
            $stability -= 0.05;
        }

        return new FitnessWeights(
            complexity: $complexity,
            stability: $stability,
            regulation: $regulation
        );
    }
}
