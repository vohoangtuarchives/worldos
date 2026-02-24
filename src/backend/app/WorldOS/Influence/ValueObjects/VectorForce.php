<?php

declare(strict_types=1);

namespace App\WorldOS\Influence\ValueObjects;

use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * VectorForce — a force/delta to be applied to WorldStateVector.
 *
 * Represents the influence of a single factor (Scar, Myth, Attractor, Narrative)
 * on the civilization state. Forces are additive and can be combined.
 *
 * From docs §16.4: Pipeline aggregates VectorForce from each influence.
 *
 * Pure PHP — NO Laravel dependencies.
 */
final readonly class VectorForce
{
    public function __construct(
        public float $deltaEntropy = 0.0,
        public float $deltaOrder = 0.0,
        public float $deltaCohesion = 0.0,
        public float $deltaInnovation = 0.0,
        public float $deltaInequality = 0.0,
        public float $deltaLegitimacy = 0.0,
        public float $deltaTrauma = 0.0,
        public string $source = '',
    ) {
    }

    /**
     * Combine two forces (additive).
     */
    public function combine(self $other): self
    {
        return new self(
            deltaEntropy: $this->deltaEntropy + $other->deltaEntropy,
            deltaOrder: $this->deltaOrder + $other->deltaOrder,
            deltaCohesion: $this->deltaCohesion + $other->deltaCohesion,
            deltaInnovation: $this->deltaInnovation + $other->deltaInnovation,
            deltaInequality: $this->deltaInequality + $other->deltaInequality,
            deltaLegitimacy: $this->deltaLegitimacy + $other->deltaLegitimacy,
            deltaTrauma: $this->deltaTrauma + $other->deltaTrauma,
            source: trim($this->source . ' + ' . $other->source, ' + '),
        );
    }

    /**
     * Scale the force by a factor.
     */
    public function scale(float $factor): self
    {
        return new self(
            deltaEntropy: $this->deltaEntropy * $factor,
            deltaOrder: $this->deltaOrder * $factor,
            deltaCohesion: $this->deltaCohesion * $factor,
            deltaInnovation: $this->deltaInnovation * $factor,
            deltaInequality: $this->deltaInequality * $factor,
            deltaLegitimacy: $this->deltaLegitimacy * $factor,
            deltaTrauma: $this->deltaTrauma * $factor,
            source: $this->source,
        );
    }

    /**
     * Get the total magnitude of all deltas.
     */
    public function magnitude(): float
    {
        return abs($this->deltaEntropy)
            + abs($this->deltaOrder)
            + abs($this->deltaCohesion)
            + abs($this->deltaInnovation)
            + abs($this->deltaInequality)
            + abs($this->deltaLegitimacy)
            + abs($this->deltaTrauma);
    }

    /**
     * Zero force (no influence).
     */
    public static function zero(): self
    {
        return new self();
    }

    /**
     * Apply this force to a WorldStateVector, returning a new vector.
     */
    public function applyTo(WorldStateVector $state): WorldStateVector
    {
        return new WorldStateVector(
            entropy: self::clamp($state->entropy + $this->deltaEntropy),
            order: self::clamp($state->order + $this->deltaOrder),
            cohesion: self::clamp($state->cohesion + $this->deltaCohesion),
            innovation: self::clamp($state->innovation + $this->deltaInnovation),
            inequality: self::clamp($state->inequality + $this->deltaInequality),
            legitimacy: self::clamp($state->legitimacy + $this->deltaLegitimacy),
            trauma: self::clamp($state->trauma + $this->deltaTrauma),
        );
    }

    private static function clamp(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }
}
