<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\ValueObject;

/**
 * Energy dimensions for cosmology seed.
 */
readonly class EnergyVector
{
    public function __construct(
        public string $manifestationType = 'kinetic',
        public float $accessibilityIndex = 0.5,
        public string $scalingCurve = 'linear',
        public float $saturationThreshold = 0.8,
        public float $mutationPotential = 0.5,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['manifestation_type'] ?? 'kinetic'),
            (float) ($data['accessibility_index'] ?? 0.5),
            (string) ($data['scaling_curve'] ?? 'linear'),
            (float) ($data['saturation_threshold'] ?? 0.8),
            (float) ($data['mutation_potential'] ?? 0.5),
        );
    }

    public function toArray(): array
    {
        return [
            'manifestation_type' => $this->manifestationType,
            'accessibility_index' => $this->accessibilityIndex,
            'scaling_curve' => $this->scalingCurve,
            'saturation_threshold' => $this->saturationThreshold,
            'mutation_potential' => $this->mutationPotential,
        ];
    }
}
