<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Fitness\ValueObject;

final class UniverseFitness
{
    public function __construct(
        public readonly float $stabilityScore,
        public readonly float $complexityScore,
        public readonly float $entropyRegulationScore,
        public readonly float $regimeDiversityScore,
        public readonly float $mythCoherenceScore,
        public readonly float $totalScore,
    ) {
    }
}
