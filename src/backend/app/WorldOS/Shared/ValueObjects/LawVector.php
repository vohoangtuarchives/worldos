<?php

declare(strict_types=1);

namespace App\WorldOS\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Law Vector — 17D parameter space (θ₁-θ₁₇).
 *
 * Defines the "physics" of a World. All dimensions normalized [0,1].
 * Immutable Value Object — any mutation produces a new instance.
 *
 * Groups:
 *   θ₁-θ₅:  Fundamental Physics
 *   θ₆-θ₉:  Structure & Stability
 *   θ₁₀-θ₁₃: Biological & Cognitive
 *   θ₁₄-θ₁₇: Cultural & Meta
 */
final readonly class LawVector
{
    public function __construct(
        public float $dimensionality,        // θ₁  — Spatial complexity
        public float $causalityRigidity,     // θ₂  — How strict cause→effect
        public float $energyStability,       // θ₃  — Energy conservation strength
        public float $interactionStrength,   // θ₄  — Force coupling constant
        public float $entropyGrowth,         // θ₅  — Rate of disorder increase
        public float $matterComplexity,      // θ₆  — Structural complexity ceiling
        public float $selfOrganization,      // θ₇  — Spontaneous order tendency
        public float $stabilityBasinDepth,   // θ₈  — Resistance to perturbation
        public float $collapseProbability,   // θ₉  — Fragility of complex structures
        public float $abiogenesis,           // θ₁₀ — Probability of life emergence
        public float $mutationVolatility,    // θ₁₁ — Variation rate in replication
        public float $adaptationEfficiency,  // θ₁₂ — Selection effectiveness
        public float $cognitiveCeiling,      // θ₁₃ — Max neural complexity
        public float $mythFormation,         // θ₁₄ — Narrative construction tendency
        public float $memoryPersistence,     // θ₁₅ — Cultural memory depth
        public float $techAccumulationRate,  // θ₁₆ — Technology growth rate
        public float $metaSystemAwareness,   // θ₁₇ — Self-reflection capacity
    ) {
        $this->validateAllDimensions();
    }

    /**
     * Create from associative array.
     *
     * @param array<string, float> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dimensionality: (float) ($data['dimensionality'] ?? $data['theta_1'] ?? 0.0),
            causalityRigidity: (float) ($data['causality_rigidity'] ?? $data['theta_2'] ?? 0.0),
            energyStability: (float) ($data['energy_stability'] ?? $data['theta_3'] ?? 0.0),
            interactionStrength: (float) ($data['interaction_strength'] ?? $data['theta_4'] ?? 0.0),
            entropyGrowth: (float) ($data['entropy_growth'] ?? $data['theta_5'] ?? 0.0),
            matterComplexity: (float) ($data['matter_complexity'] ?? $data['theta_6'] ?? 0.0),
            selfOrganization: (float) ($data['self_organization'] ?? $data['theta_7'] ?? 0.0),
            stabilityBasinDepth: (float) ($data['stability_basin_depth'] ?? $data['theta_8'] ?? 0.0),
            collapseProbability: (float) ($data['collapse_probability'] ?? $data['theta_9'] ?? 0.0),
            abiogenesis: (float) ($data['abiogenesis'] ?? $data['theta_10'] ?? 0.0),
            mutationVolatility: (float) ($data['mutation_volatility'] ?? $data['theta_11'] ?? 0.0),
            adaptationEfficiency: (float) ($data['adaptation_efficiency'] ?? $data['theta_12'] ?? 0.0),
            cognitiveCeiling: (float) ($data['cognitive_ceiling'] ?? $data['theta_13'] ?? 0.0),
            mythFormation: (float) ($data['myth_formation'] ?? $data['theta_14'] ?? 0.0),
            memoryPersistence: (float) ($data['memory_persistence'] ?? $data['theta_15'] ?? 0.0),
            techAccumulationRate: (float) ($data['tech_accumulation_rate'] ?? $data['theta_16'] ?? 0.0),
            metaSystemAwareness: (float) ($data['meta_system_awareness'] ?? $data['theta_17'] ?? 0.0),
        );
    }

    /**
     * Serialize to array for storage.
     *
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return [
            'dimensionality' => $this->dimensionality,
            'causality_rigidity' => $this->causalityRigidity,
            'energy_stability' => $this->energyStability,
            'interaction_strength' => $this->interactionStrength,
            'entropy_growth' => $this->entropyGrowth,
            'matter_complexity' => $this->matterComplexity,
            'self_organization' => $this->selfOrganization,
            'stability_basin_depth' => $this->stabilityBasinDepth,
            'collapse_probability' => $this->collapseProbability,
            'abiogenesis' => $this->abiogenesis,
            'mutation_volatility' => $this->mutationVolatility,
            'adaptation_efficiency' => $this->adaptationEfficiency,
            'cognitive_ceiling' => $this->cognitiveCeiling,
            'myth_formation' => $this->mythFormation,
            'memory_persistence' => $this->memoryPersistence,
            'tech_accumulation_rate' => $this->techAccumulationRate,
            'meta_system_awareness' => $this->metaSystemAwareness,
        ];
    }

    /**
     * Get a specific dimension by index (1-based: θ₁ to θ₁₇).
     */
    public function getDimension(int $index): float
    {
        return match ($index) {
            1 => $this->dimensionality,
            2 => $this->causalityRigidity,
            3 => $this->energyStability,
            4 => $this->interactionStrength,
            5 => $this->entropyGrowth,
            6 => $this->matterComplexity,
            7 => $this->selfOrganization,
            8 => $this->stabilityBasinDepth,
            9 => $this->collapseProbability,
            10 => $this->abiogenesis,
            11 => $this->mutationVolatility,
            12 => $this->adaptationEfficiency,
            13 => $this->cognitiveCeiling,
            14 => $this->mythFormation,
            15 => $this->memoryPersistence,
            16 => $this->techAccumulationRate,
            17 => $this->metaSystemAwareness,
            default => throw new InvalidArgumentException(
                "Law dimension index must be 1-17, got: {$index}"
            ),
        };
    }

    /**
     * Critical Law Subspace — the 4 dimensions that determine 80% of behavior.
     * θ₅ (Entropy Growth), θ₁₃ (Cognitive Ceiling), θ₁₆ (Tech Accumulation), θ₁₇ (Meta-Awareness).
     *
     * @return array<string, float>
     */
    public function criticalSubspace(): array
    {
        return [
            'entropy_growth' => $this->entropyGrowth,
            'cognitive_ceiling' => $this->cognitiveCeiling,
            'tech_accumulation_rate' => $this->techAccumulationRate,
            'meta_system_awareness' => $this->metaSystemAwareness,
        ];
    }

    /**
     * Equals comparison.
     */
    public function equals(self $other): bool
    {
        return $this->toArray() === $other->toArray();
    }

    private function validateAllDimensions(): void
    {
        $dimensions = $this->toArray();

        foreach ($dimensions as $name => $value) {
            if ($value < 0.0 || $value > 1.0) {
                throw new InvalidArgumentException(
                    "Law dimension '{$name}' must be in [0.0, 1.0], got: {$value}"
                );
            }
        }
    }
}
