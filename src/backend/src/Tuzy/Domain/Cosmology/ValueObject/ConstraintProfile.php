<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmology\ValueObject;

/**
 * Constraint Profile — immutable from Genesis.
 * Maps Author Intent axes to pressure coefficients and inertia.
 * Tick engine reads only; never modify after World/Universe creation.
 */
readonly class ConstraintProfile
{
    public function __construct(
        public string $narrativeDensity,
        public string $powerGradient,
        public string $resourceDensity,
        public string $perceptionComplexity,
        public string $conflictIntensity,
        public string $socialThickness,
        public string $mythologyLayer,
    ) {
    }

    public static function fromIntent(array $intent): self
    {
        return new self(
            $intent['narrative_density'] ?? 'medium',
            $intent['power_gradient'] ?? 'medium',
            $intent['resource_density'] ?? 'medium',
            $intent['perception_complexity'] ?? 'medium',
            $intent['conflict_intensity'] ?? 'medium',
            $intent['social_thickness'] ?? 'medium',
            $intent['mythology_layer'] ?? 'subtle',
        );
    }

    public function toArray(): array
    {
        return [
            'narrative_density' => $this->narrativeDensity,
            'power_gradient' => $this->powerGradient,
            'resource_density' => $this->resourceDensity,
            'perception_complexity' => $this->perceptionComplexity,
            'conflict_intensity' => $this->conflictIntensity,
            'social_thickness' => $this->socialThickness,
            'mythology_layer' => $this->mythologyLayer,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['narrative_density'] ?? 'medium',
            $data['power_gradient'] ?? 'medium',
            $data['resource_density'] ?? 'medium',
            $data['perception_complexity'] ?? 'medium',
            $data['conflict_intensity'] ?? 'medium',
            $data['social_thickness'] ?? 'medium',
            $data['mythology_layer'] ?? 'subtle',
        );
    }

    public function alpha(): float
    {
        return match ($this->powerGradient) {
            'steep' => 0.35,
            'gentle' => 0.15,
            default => 0.25,
        };
    }

    public function beta(): float
    {
        return match ($this->resourceDensity) {
            'scarce' => 0.35,
            'abundant' => 0.15,
            default => 0.25,
        };
    }

    public function gamma(): float
    {
        return match ($this->perceptionComplexity) {
            'complex' => 0.30,
            'simple' => 0.15,
            default => 0.22,
        };
    }

    public function delta(): float
    {
        return match ($this->socialThickness) {
            'deep' => 0.25,
            'light' => 0.35,
            default => 0.28,
        };
    }

    public function inertia(): float
    {
        return match ($this->conflictIntensity) {
            'high' => 0.6,
            'low' => 0.85,
            default => 0.7,
        };
    }

    public function feedbackK(): float
    {
        return match ($this->conflictIntensity) {
            'high' => 0.4,
            'low' => 0.15,
            default => 0.25,
        };
    }
}
