<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\ValueObject;

/**
 * Ontology dimensions for cosmology seed.
 */
readonly class OntologyVector
{
    public function __construct(
        public float $energyDensity = 0.5,
        public float $mortalityWeight = 0.5,
        public float $causalityStrength = 0.5,
        public float $consciousnessImprint = 0.5,
        public float $entropyPressure = 0.5,
        public float $realityRigidity = 0.5,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['energy_density'] ?? 0.5),
            (float) ($data['mortality_weight'] ?? 0.5),
            (float) ($data['causality_strength'] ?? 0.5),
            (float) ($data['consciousness_imprint'] ?? 0.5),
            (float) ($data['entropy_pressure'] ?? 0.5),
            (float) ($data['reality_rigidity'] ?? 0.5),
        );
    }

    public function toArray(): array
    {
        return [
            'energy_density' => $this->energyDensity,
            'mortality_weight' => $this->mortalityWeight,
            'causality_strength' => $this->causalityStrength,
            'consciousness_imprint' => $this->consciousnessImprint,
            'entropy_pressure' => $this->entropyPressure,
            'reality_rigidity' => $this->realityRigidity,
        ];
    }
}
