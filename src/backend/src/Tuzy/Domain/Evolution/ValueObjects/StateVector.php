<?php

namespace Tuzy\Domain\Evolution\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class StateVector implements Arrayable
{
    public function __construct(
        public readonly float $coherence,
        public readonly float $entropy,
        public readonly float $belief_mass,
        public readonly float $resource_flow,
        public readonly float $stability,
        public readonly float $innovation_rate,
        public readonly float $contradiction_index = 0.0,
        public readonly array $latent_variables = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            coherence: (float) ($data['coherence'] ?? 0.5),
            entropy: (float) ($data['entropy'] ?? 0.1),
            belief_mass: (float) ($data['belief_mass'] ?? 0.1),
            resource_flow: (float) ($data['resource_flow'] ?? 0.5),
            stability: (float) ($data['stability'] ?? 0.8),
            innovation_rate: (float) ($data['innovation_rate'] ?? 0.2),
            contradiction_index: (float) ($data['contradiction_index'] ?? 0.0),
            latent_variables: $data['latent_variables'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'coherence' => $this->coherence,
            'entropy' => $this->entropy,
            'belief_mass' => $this->belief_mass,
            'resource_flow' => $this->resource_flow,
            'stability' => $this->stability,
            'innovation_rate' => $this->innovation_rate,
            'contradiction_index' => $this->contradiction_index,
            'latent_variables' => $this->latent_variables,
        ];
    }

    public function applyDelta(array $delta): self
    {
        // Clamp values between 0.0 and 1.0 (except maybe latent?)
        // Clamp values between 0.0 and 1.0 (except maybe latent?)

        return new self(
            coherence: max(0.0, min(1.0, $this->coherence + ($delta['coherence'] ?? 0))),
            entropy: max(0.0, min(1.0, $this->entropy + ($delta['entropy'] ?? 0))),
            belief_mass: max(0.0, min(1.0, $this->belief_mass + ($delta['belief_mass'] ?? 0))),
            resource_flow: max(0.0, min(1.0, $this->resource_flow + ($delta['resource_flow'] ?? 0))),
            stability: max(0.0, min(1.0, $this->stability + ($delta['stability'] ?? 0))),
            innovation_rate: max(0.0, min(1.0, $this->innovation_rate + ($delta['innovation_rate'] ?? 0))),
            contradiction_index: max(0.0, min(1.0, $this->contradiction_index + ($delta['contradiction_index'] ?? 0))),
            latent_variables: array_merge($this->latent_variables, $delta['latent_variables'] ?? [])
        );
    }
}
