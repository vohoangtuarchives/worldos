<?php

namespace Tuzy\Domain\Evolution\ValueObject;

class EpistemicVector
{
    public function __construct(
        public readonly float $epistemicStability,
        public readonly float $beliefFragmentation,
        public readonly float $rationalityBias,
        public readonly float $mysticismOpenness,
        public readonly float $historicalCertainty
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['epistemic_stability'],
            $data['belief_fragmentation'],
            $data['rationality_bias'],
            $data['mysticism_openness'],
            $data['historical_certainty']
        );
    }
    
    public function toArray(): array
    {
        return [
            'epistemic_stability' => $this->epistemicStability,
            'belief_fragmentation' => $this->beliefFragmentation,
            'rationality_bias' => $this->rationalityBias,
            'mysticism_openness' => $this->mysticismOpenness,
            'historical_certainty' => $this->historicalCertainty,
        ];
    }
}


