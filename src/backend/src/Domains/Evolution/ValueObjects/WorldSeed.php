<?php

namespace WorldOS\Domains\Evolution\ValueObjects;

class WorldSeed
{
    public function __construct(
        public readonly string $archetypeId,
        public readonly OntologyVector $ontology,
        public readonly EpistemicVector $epistemic,
        public readonly CivilizationVector $civilization,
        public readonly EnergyVector $energy,
        public readonly array $driftProfile,
        public readonly ?string $seedHash = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['archetype_id'] ?? $data['archetype'],
            OntologyVector::fromArray($data['ontology']),
            EpistemicVector::fromArray($data['epistemic']),
            CivilizationVector::fromArray($data['civilization']),
            EnergyVector::fromArray($data['energy']),
            $data['drift_profile'] ?? [],
            $data['seed_hash'] ?? uniqid('seed_')
        );
    }
    
    public function toArray(): array
    {
        return [
            'archetype_id' => $this->archetypeId,
            'ontology' => $this->ontology->toArray(),
            'epistemic' => $this->epistemic->toArray(),
            'civilization' => $this->civilization->toArray(),
            'energy' => $this->energy->toArray(),
            'drift_profile' => $this->driftProfile,
            'seed_hash' => $this->seedHash,
        ];
    }
}


