<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmology\ValueObject;

/**
 * Cosmology world seed: archetype + ontology, epistemic, civilization, energy vectors.
 */
readonly class WorldSeed
{
    public function __construct(
        public string $archetypeId,
        public OntologyVector $ontology,
        public EpistemicVector $epistemic,
        public CivilizationVector $civilization,
        public EnergyVector $energy,
        public array $driftProfile = [],
        public ?string $seedHash = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['archetype_id'] ?? $data['archetype'] ?? 'default'),
            OntologyVector::fromArray($data['ontology'] ?? []),
            EpistemicVector::fromArray($data['epistemic'] ?? []),
            CivilizationVector::fromArray($data['civilization'] ?? []),
            EnergyVector::fromArray($data['energy'] ?? []),
            $data['drift_profile'] ?? [],
            $data['seed_hash'] ?? null,
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
