<?php

namespace WorldOS\Domains\Evolution\ValueObjects;

class EnergyVector
{
    public function __construct(
        public readonly string $manifestationType,
        public readonly float $accessibilityIndex,
        public readonly string $scalingCurve,
        public readonly float $saturationThreshold,
        public readonly float $mutationPotential
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['manifestation_type'],
            $data['accessibility_index'],
            $data['scaling_curve'],
            $data['saturation_threshold'],
            $data['mutation_potential']
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


