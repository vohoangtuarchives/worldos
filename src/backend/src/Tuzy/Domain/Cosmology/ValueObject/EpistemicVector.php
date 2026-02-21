<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmology\ValueObject;

/**
 * Epistemic dimensions for cosmology seed.
 */
readonly class EpistemicVector
{
    public function __construct(
        public float $epistemicStability = 0.5,
        public float $beliefFragmentation = 0.0,
        public float $rationalityBias = 0.5,
        public float $mysticismOpenness = 0.5,
        public float $historicalCertainty = 0.5,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['epistemic_stability'] ?? 0.5),
            (float) ($data['belief_fragmentation'] ?? 0.0),
            (float) ($data['rationality_bias'] ?? 0.5),
            (float) ($data['mysticism_openness'] ?? 0.5),
            (float) ($data['historical_certainty'] ?? 0.5),
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
