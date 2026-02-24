<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Saga\ValueObject;

use InvalidArgumentException;

/**
 * SagaMetrics — Tracks narrative performance factors for a Saga.
 */
final class SagaMetrics
{
    private function __construct(
        private readonly float $diversityScore,
        private readonly float $narrativePressureIndex
    ) {
        if ($diversityScore < 0.0 || $diversityScore > 1.0) {
            throw new InvalidArgumentException("Diversity score must be [0.0, 1.0]");
        }
    }

    public static function create(float $diversityScore, float $narrativePressureIndex): self
    {
        return new self($diversityScore, $narrativePressureIndex);
    }

    public static function genesis(): self
    {
        return new self(
            1.0,  // Max diversity at birth
            0.5   // Neutral NPI at birth
        );
    }

    public function getDiversityScore(): float
    {
        return $this->diversityScore;
    }

    /**
     * NPI evaluates the unspent "tension currency". If NPI drops too low for too long, 
     * the Saga destabilizes because the story gets boring or stagnant.
     */
    public function getNarrativePressureIndex(): float
    {
        return $this->narrativePressureIndex;
    }

    /**
     * Evolves the metrics. NPI drifts according to a delta, simulating story progression.
     */
    public function withEvolvedNpi(float $npiDelta): self
    {
        // NPI isn't strictly bounded to 1.0, but practically usually [0.0, 2.0]
        $newNpi = max(0.0, $this->narrativePressureIndex + $npiDelta);
        return new self($this->diversityScore, $newNpi);
    }

    public function toArray(): array
    {
        return [
            'diversity_score'          => $this->diversityScore,
            'narrative_pressure_index' => $this->narrativePressureIndex,
        ];
    }
}
