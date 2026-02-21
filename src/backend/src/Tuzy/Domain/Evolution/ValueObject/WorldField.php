<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\ValueObject;

use InvalidArgumentException;

/**
 * WorldField - The "Soul" of the world.
 * Stores persistent physical/metaphysical constants that survive across civilizations.
 * 
 * The Influence Vector determines the "Space of Possibility":
 * - magic: Density of supernatural energy (Cultivation/Magic)
 * - tech: Physical constant alignment for material science (Industrial/Cyber)
 * - psionic: Consciousness-matter resonance (Hive-minds/Psychic)
 * - divine: Connection to higher-plane entities (Theocracy/Exogenous influence)
 * - chaos: Rate of mutation and unpredictability
 */
final class WorldField
{
    public function __construct(
        public readonly array $influenceVector, // Map<string, float>
        public readonly string $mutationBias = 'balanced',
        public readonly float $fragilityIndex = 0.5,
        public readonly array $residualTraces = [] // History remnants
    ) {
        $this->validate();
    }

    public static function default(): self
    {
        return new self(
            influenceVector: [
                'magic' => 0.1,
                'tech' => 0.5,
                'psionic' => 0.1,
                'divine' => 0.05,
                'chaos' => 0.2
            ],
            mutationBias: 'balanced'
        );
    }

    public function withShift(array $shift): self
    {
        $newVector = $this->influenceVector;
        foreach ($shift as $key => $delta) {
            if (isset($newVector[$key])) {
                $newVector[$key] = max(0.0, min(2.0, $newVector[$key] + $delta));
            }
        }

        return new self(
            influenceVector: $newVector,
            mutationBias: $this->mutationBias,
            fragilityIndex: $this->fragilityIndex,
            residualTraces: $this->residualTraces
        );
    }

    public function toArray(): array
    {
        return [
            'influence_vector' => $this->influenceVector,
            'mutation_bias' => $this->mutationBias,
            'fragility_index' => $this->fragilityIndex,
            'residual_traces' => $this->residualTraces,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            influenceVector: $data['influence_vector'] ?? [],
            mutationBias: $data['mutation_bias'] ?? 'balanced',
            fragilityIndex: (float) ($data['fragility_index'] ?? 0.5),
            residualTraces: $data['residual_traces'] ?? []
        );
    }

    private function validate(): void
    {
        foreach ($this->influenceVector as $key => $value) {
            if ($value < 0.0) {
                // We allow values up to 2.0 for extreme divergence cases (High concentration fields)
            }
        }
    }
}
