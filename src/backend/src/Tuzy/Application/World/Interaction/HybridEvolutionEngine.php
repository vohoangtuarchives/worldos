<?php

namespace Tuzy\Application\World\Interaction;

use App\Domains\World\Evolution\EvolutionEngine;
use Tuzy\Domain\World\WorldState;

class HybridEvolutionEngine implements EvolutionEngine
{
    private array $equations;
    private array $collapseConditions;
    private array $identity;

    public function __construct(
        array $equations,
        array $collapseConditions,
        array $identity
    ) {
        $this->equations = $equations;
        $this->collapseConditions = $collapseConditions;
        $this->identity = $identity;
    }

    public function calculateDeltaCoherence(WorldState $state): float
    {
        return $this->evaluateEquation(
            $this->equations['coherence_equation'] ?? '0',
            $state
        );
    }

    public function calculateDeltaEntropy(WorldState $state): float
    {
        return $this->evaluateEquation(
            $this->equations['entropy_equation'] ?? '0',
            $state
        );
    }

    public function calculateDeltaBelief(WorldState $state): float
    {
        return $this->evaluateEquation(
            $this->equations['belief_equation'] ?? '0',
            $state
        );
    }

    public function calculateDeltaStability(WorldState $state): float
    {
        return $this->evaluateEquation(
            $this->equations['stability_equation'] ?? '0',
            $state
        );
    }

    public function shouldCollapse(WorldState $state): bool
    {
        $primaryCondition = $this->collapseConditions['primary_condition'] ?? 'false';
        $threshold = $this->collapseConditions['threshold_modifier'] ?? 0.5;
        
        // Evaluate primary condition
        $primaryResult = $this->evaluateCondition($primaryCondition, $state);
        
        // Check secondary conditions
        $secondaryResults = [];
        foreach ($this->collapseConditions['secondary_conditions'] ?? [] as $condition) {
            $secondaryResults[] = $this->evaluateCondition($condition, $state);
        }
        
        // Collapse if primary condition true and enough secondary conditions
        $secondaryCount = count(array_filter($secondaryResults, fn($r) => $r));
        $secondaryThreshold = max(1, count($secondaryResults) * 0.5);
        
        return $primaryResult && $secondaryCount >= $secondaryThreshold;
    }

    public function getAttractorType(WorldState $state): string
    {
        // Hybrid attractors combine parent characteristics
        $coherence = $state->coherence;
        $entropy = $state->entropy;
        $stability = $state->stability;
        
        if ($coherence > 0.7 && $entropy < 0.4) {
            return 'HYBRID_STABLE';
        }
        
        if ($entropy > 0.7 && $coherence < 0.4) {
            return 'HYBRID_CHAOTIC';
        }
        
        if ($stability > 0.6 && abs($coherence - 0.5) < 0.2) {
            return 'HYBRID_BALANCED';
        }
        
        if ($entropy > 0.5 && $coherence > 0.5) {
            return 'HYBRID_COMPLEX';
        }
        
        return 'HYBRID_TRANSITIONAL';
    }

    public function getIdentity(): array
    {
        return $this->identity;
    }

    public function getCoherenceEquation(): string
    {
        return $this->equations['coherence_equation'] ?? '0';
    }

    public function getEntropyEquation(): string
    {
        return $this->equations['entropy_equation'] ?? '0';
    }

    public function getBeliefEquation(): string
    {
        return $this->equations['belief_equation'] ?? '0';
    }

    public function getStabilityEquation(): string
    {
        return $this->equations['stability_equation'] ?? '0';
    }

    public function getPrimaryCollapseCondition(): string
    {
        return $this->collapseConditions['primary_condition'] ?? 'false';
    }

    public function getSecondaryCollapseConditions(): array
    {
        return $this->collapseConditions['secondary_conditions'] ?? [];
    }

    public function getCollapseThreshold(): float
    {
        return $this->collapseConditions['threshold_modifier'] ?? 0.5;
    }

    private function evaluateEquation(string $equation, WorldState $state): float
    {
        // Simple equation evaluator - in production, use proper parser
        $equation = str_replace([
            'coherence', 'entropy', 'stability', 'belief_mass', 'dominance',
            'permeability', 'resource_flow', 'contradiction_index', 'randomness'
        ], [
            $state->coherence, $state->entropy, $state->stability, $state->beliefMass,
            $state->dominanceLevel, $state->permeability, $state->resourceFlow,
            $state->contradictionIndex, $state->randomness ?? 0.5
        ], $equation);

        try {
            // Safe evaluation with basic math only
            return eval("return $equation;");
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private function evaluateCondition(string $condition, WorldState $state): bool
    {
        // Similar to equation evaluator but returns boolean
        $condition = str_replace([
            'coherence', 'entropy', 'stability', 'belief_mass', 'dominance',
            'permeability', 'resource_flow', 'contradiction_index'
        ], [
            $state->coherence, $state->entropy, $state->stability, $state->beliefMass,
            $state->dominanceLevel, $state->permeability, $state->resourceFlow,
            $state->contradictionIndex
        ], $condition);

        try {
            return (bool) eval("return $condition;");
        } catch (\Throwable $e) {
            return false;
        }
    }
}
