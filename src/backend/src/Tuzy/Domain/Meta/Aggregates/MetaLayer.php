<?php

namespace Tuzy\Domain\Meta\Aggregates;

use Tuzy\Domain\Meta\Policies\HomeostasisPolicy;
use Tuzy\Domain\Replay\DeterministicRandom;
use Illuminate\Support\Facades\Log;

class MetaLayer
{
    // Physics State
    public float $chaosPool = 0.0;
    public float $entropyPressure = 0.0;
    public float $resourceFlux = 0.5;

    // Ideology Vector (Normalized 0.0 - 1.0)
    public array $ideologyVector = [
        'order' => 0.5,
        'chaos' => 0.5,
        'expansion' => 0.5,
        'consolidation' => 0.5,
        'diversity' => 0.5,
    ];

    // Dynamics
    public float $aggressionIndex = 0.0;
    public float $stabilityIndex = 0.5;
    public float $mutationBias = 0.01;

    // Era Tracking
    public int $currentEraIndex = 0;
    public string $currentEraPhase = 'rise'; // rise, peak, decline, rebirth

    // Private helpers
    private DeterministicRandom $random;
    private HomeostasisPolicy $homeostasisPolicy;

    public function __construct(
        DeterministicRandom $random = null,
        HomeostasisPolicy $homeostasisPolicy = null
    ) {
        $this->random = $random ?? new DeterministicRandom(0); // Default deterministic
        $this->homeostasisPolicy = $homeostasisPolicy ?? new HomeostasisPolicy();
    }

    /**
     * Restore state from snapshot/DB
     */
    public function hydrate(array $state): void
    {
        $this->chaosPool = $state['chaos_pool'] ?? 0.0;
        $this->entropyPressure = $state['entropy_pressure'] ?? 0.0;
        $this->resourceFlux = $state['resource_flux'] ?? 0.5;
        $this->ideologyVector = $state['ideology_vector'] ?? $this->ideologyVector;
        $this->aggressionIndex = $state['aggression_index'] ?? 0.0;
        $this->stabilityIndex = $state['stability_index'] ?? 0.5;
        $this->mutationBias = $state['mutation_bias'] ?? 0.01;
        $this->currentEraIndex = $state['current_era_index'] ?? 0;
    }

    /**
     * Main Evolution Tick
     */
    public function evolve(): void
    {
        // 1. Decay Dynamics (Natural Entropy)
        $this->chaosPool *= 0.98;
        $this->entropyPressure *= 0.99;
        
        // 2. Apply Homeostasis (Restoring Force)
        $restoringForces = $this->homeostasisPolicy->calculateRestoringForce(
            $this->ideologyVector,
            $this->currentEraIndex
        );

        foreach ($restoringForces as $axis => $force) {
            $this->ideologyVector[$axis] += $force;
            // Clamp
            $this->ideologyVector[$axis] = max(0.2, min(0.8, $this->ideologyVector[$axis]));
        }

        // 3. Random Flux (Micro-noise)
        $noise = ($this->random->nextFloat() - 0.5) * 0.01;
        $this->resourceFlux += $noise;
        $this->resourceFlux = max(0.1, min(0.9, $this->resourceFlux));

        // 4. Update Derived Indices
        $this->updateStabilityIndex();
    }

    /**
     * Apply Impulse from a World (e.g. Collapse, War)
     */
    public function applyImpulse(string $type, float $strength, array $payload = []): void
    {
        switch ($type) {
            case 'collapse':
                $this->chaosPool += $strength * 0.5;
                $this->entropyPressure += $strength * 0.2;
                // Shift ideology towards consolidation
                $this->ideologyVector['consolidation'] += $strength * 0.01;
                break;
            
            case 'expansion_boom':
                $this->resourceFlux -= $strength * 0.1;
                $this->ideologyVector['expansion'] += $strength * 0.01;
                break;
                
            case 'myth_resonance':
                // Myth alters ideology directly
                foreach ($payload['vector'] ?? [] as $axis => $delta) {
                    if (isset($this->ideologyVector[$axis])) {
                        $this->ideologyVector[$axis] += $delta * $strength;
                    }
                }
                break;
        }

        // Normalize vector lightly to prevent runaway
        $this->normalizeIdeology();
    }

    private function updateStabilityIndex(): void
    {
        // Stability is inverse of chaos and high entropy
        $chaosFactor = $this->chaosPool / 100.0; // Assume 100 is max relevant
        $this->stabilityIndex = 1.0 - ($chaosFactor * 0.5 + $this->entropyPressure * 0.5);
        $this->stabilityIndex = max(0.0, min(1.0, $this->stabilityIndex));
    }

    private function normalizeIdeology(): void
    {
        foreach ($this->ideologyVector as $k => $v) {
            $this->ideologyVector[$k] = max(0.1, min(0.9, $v));
        }
    }

    public function exportState(): array
    {
        return [
            'chaos_pool' => round($this->chaosPool, 4),
            'entropy_pressure' => round($this->entropyPressure, 4),
            'resource_flux' => round($this->resourceFlux, 4),
            'ideology_vector' => $this->ideologyVector,
            'aggression_index' => round($this->aggressionIndex, 4),
            'stability_index' => round($this->stabilityIndex, 4),
            'mutation_bias' => round($this->mutationBias, 4),
            'current_era_index' => $this->currentEraIndex,
        ];
    }

    /**
     * Check if extinction is triggered
     */
    public function checkExtinction(): ?float
    {
        $policy = new \Tuzy\Domain\Meta\Policies\ExtinctionPolicy();
        if ($policy->shouldTriggerExtinction($this)) {
            return $policy->calculateSeverity($this);
        }
        return null;
    }

    /**
     * Attempt to canonize a world's archetype
     */
    public function attemptCanonization(\App\Models\World $world, array $mythProfile): ?\App\Models\SacredArchetype
    {
        $policy = new \Tuzy\Domain\Meta\Policies\CanonizationPolicy();
        if ($policy->shouldCanonize($world, $mythProfile)) {
            // Create Sacred Archetype
            // Use a repository or factory ideally, direct model for now
            return \App\Models\SacredArchetype::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'parent_archetype_key' => $world->archetype_id ?? 'unknown',
                'name' => $world->name . "'s Exemplar",
                'sacred_strength' => $policy->calculateSacredStrength($world, $mythProfile),
                'canonized_at_tick' => $this->currentEraIndex, // Using era index as tick proxy? No, use global tick.
                'status' => 'active',
                'myth_profile' => $mythProfile,
            ]);
        }
        return null;
    }
}
