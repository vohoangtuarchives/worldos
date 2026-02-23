<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Arc\ValueObject;

use InvalidArgumentException;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

/**
 * ChapterNode — Represents a localized narrative beat within an Arc.
 * Extracted by analyzing the most volatile dimension at a specific tick.
 * Immutable Value Object.
 */
final class ChapterNode
{
    private function __construct(
        private readonly int $tick,
        private readonly float $intensity, // The degree of change/volatility
        private readonly string $dominantDimension
    ) {
    }

    public static function create(int $tick, float $intensity, string $dominantDimension): self
    {
        if ($tick < 0) {
            throw new InvalidArgumentException("Tick must be a positive integer.");
        }
        if ($intensity < 0.0) {
            throw new InvalidArgumentException("Intensity cannot be negative.");
        }
        if (!array_key_exists($dominantDimension, StateVector::DEFAULT_DIMENSIONS)) {
            throw new InvalidArgumentException("Invalid dominant dimension: {$dominantDimension}");
        }

        return new self($tick, $intensity, $dominantDimension);
    }

    public function getTick(): int
    {
        return $this->tick;
    }

    public function getIntensity(): float
    {
        return $this->intensity;
    }

    public function getDominantDimension(): string
    {
        return $this->dominantDimension;
    }

    public function toArray(): array
    {
        return [
            'tick'               => $this->tick,
            'intensity'          => $this->intensity,
            'dominant_dimension' => $this->dominantDimension,
        ];
    }
}
