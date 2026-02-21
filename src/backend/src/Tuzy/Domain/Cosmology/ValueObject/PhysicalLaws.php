<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmology\ValueObject;

/**
 * PhysicalLaws (Value Object)
 * 
 * Represents the fundamental unchanging physical laws and constants of a World.
 * These are the nuclear "rules of the game" that all Universes within this World must obey.
 */
class PhysicalLaws
{
    public function __construct(
        public readonly float $entropyRate,       // How fast entropy naturally increases
        public readonly float $energyDecayRate,   // How fast energy dissipates
        public readonly float $stabilityThreshold,// Point of no return for systems
        public readonly float $causalityStrength, // Strength of cause-effect relationships
        // Reality Constraints (Sensitivity Constants)
        public readonly float $alpha = 0.25,      // Power Imbalance weight
        public readonly float $beta = 0.25,       // Resource Stress weight
        public readonly float $gamma = 0.22,      // Ideology Divergence weight
        public readonly float $delta = 0.28,      // Social Fragmentation weight
        public readonly float $feedbackK = 0.25,  // Feedback amplification factor
        public readonly float $inertiaLambda = 0.7, // Inertia for pressure smoothing
        public readonly array $customConstants = [] // Archetype-specific physics
    ) {}

    public static function default(): self
    {
        return new self(0.01, 0.02, 0.7, 1.0);
    }

    public function toArray(): array
    {
        return [
            'entropy_rate' => $this->entropyRate,
            'energy_decay_rate' => $this->energyDecayRate,
            'stability_threshold' => $this->stabilityThreshold,
            'causality_strength' => $this->causalityStrength,
            'alpha' => $this->alpha,
            'beta' => $this->beta,
            'gamma' => $this->gamma,
            'delta' => $this->delta,
            'feedback_k' => $this->feedbackK,
            'inertia_lambda' => $this->inertiaLambda,
            'custom_constants' => $this->customConstants,
        ];
    }
}
