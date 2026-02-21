<?php

namespace Tuzy\Domain\Evolution\ValueObject;

class OntologyVector
{
    public function __construct(
        public readonly float $energyDensity,
        public readonly float $mortalityWeight,
        public readonly float $causalityStrength,
        public readonly float $consciousnessImprint,
        public readonly float $entropyPressure,
        public readonly float $realityRigidity
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['energy_density'],
            $data['mortality_weight'],
            $data['causality_strength'],
            $data['consciousness_imprint'],
            $data['entropy_pressure'],
            $data['reality_rigidity']
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


