<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Hero\ValueObject;

use InvalidArgumentException;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

/**
 * HeroProfile — The immutable DNA of the Hero.
 * Generated at birth (or projection) and stays constant throughout the saga.
 */
final class HeroProfile
{
    /**
     * @param string $dominantDimension The dimension this hero acts as a projection of (e.g., 'transcendence')
     * @param float  $seedConviction    Initial belief/willpower max capacity [0.1, 1.0]
     */
    private function __construct(
        private readonly string $dominantDimension,
        private readonly float $seedConviction
    ) {
    }

    public static function create(string $dominantDimension, float $seedConviction): self
    {
        // Must be a valid dimension from the 17D standard vector
        if (!array_key_exists($dominantDimension, StateVector::DEFAULT_DIMENSIONS)) {
            throw new InvalidArgumentException("Invalid dominant dimension: {$dominantDimension}");
        }

        if ($seedConviction < 0.1 || $seedConviction > 1.0) {
            throw new InvalidArgumentException("Seed conviction must be between 0.1 and 1.0");
        }

        return new self($dominantDimension, $seedConviction);
    }

    public function getDominantDimension(): string
    {
        return $this->dominantDimension;
    }

    public function getSeedConviction(): float
    {
        return $this->seedConviction;
    }

    public function toArray(): array
    {
        return [
            'dominant_dimension' => $this->dominantDimension,
            'seed_conviction'    => $this->seedConviction,
        ];
    }
}
