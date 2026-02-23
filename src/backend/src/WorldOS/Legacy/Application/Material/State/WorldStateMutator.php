<?php

namespace WorldOS\Legacy\Application\Material\State;

/**
 * WorldStateMutator - Delta-Based State Mutation
 * 
 * Rule: NO direct state modification. ALL changes via delta.
 */
class WorldStateMutator
{
    /**
     * Apply deltas to world state and return new state.
     * 
     * @param WorldState $currentState
     * @param array $deltas Keyed by state field
     * @param array $origins Material codes that caused each delta
     * @return WorldState New state with deltas applied
     */
    public function applyDeltas(WorldState $currentState, array $deltas, array $origins = []): WorldState
    {
        // Separate deltas by component
        $coreDeltas = $this->extractComponentDeltas($deltas, [
            'population', 'subsistence_base', 'resource_pressure', 'mortality_rate', 'health_index'
        ]);

        $structuralDeltas = $this->extractComponentDeltas($deltas, [
            'inequality', 'labor_coercion', 'infrastructure_integrity',
            'centralization', 'productivity_ceiling', 'specialization_depth'
        ]);

        $symbolicDeltas = $this->extractComponentDeltas($deltas, [
            'myth_strength', 'belief_extremism', 'legitimacy', 'ritualization', 'identity_rigidity'
        ]);

        $memoryDeltas = $this->extractComponentDeltas($deltas, [
            'trauma_density', 'nostalgia_pressure', 'grievance_index',
            'historical_distortion', 'legacy_load'
        ]);

        $interactionDeltas = $this->extractComponentDeltas($deltas, [
            'external_threat', 'migration_pressure', 'trade_exposure',
            'cultural_friction', 'world_reputation'
        ]);

        // Apply deltas to each component
        $newCore = $currentState->core->applyDelta($coreDeltas);
        $newStructural = $currentState->structural->applyDelta($structuralDeltas);
        $newSymbolic = $currentState->symbolic->applyDelta($symbolicDeltas);
        $newMemory = $currentState->memory->applyDelta($memoryDeltas);
        $newInteraction = $currentState->interaction->applyDelta($interactionDeltas);

        // Recalculate meta state
        $newMeta = MetaState::calculate(
            $currentState->epoch + 1,
            $newCore,
            $newStructural,
            $newSymbolic,
            $newMemory
        );

        return $currentState->withComponents(
            core: $newCore,
            structural: $newStructural,
            symbolic: $newSymbolic,
            memory: $newMemory,
            interaction: $newInteraction,
            meta: $newMeta
        )->withEpoch($currentState->epoch + 1);
    }

    /**
     * Extract deltas for a specific component.
     */
    private function extractComponentDeltas(array $allDeltas, array $fields): array
    {
        $componentDeltas = [];

        foreach ($fields as $field) {
            if (isset($allDeltas[$field])) {
                $componentDeltas[$field] = $allDeltas[$field];
            }
        }

        return $componentDeltas;
    }

    /**
     * Validate deltas before application.
     * Ensures no single delta exceeds ±0.3 per tick.
     */
    public function validateDeltas(array $deltas): array
    {
        $validated = [];

        foreach ($deltas as $key => $value) {
            // Clamp individual deltas
            $validated[$key] = min(0.3, max(-0.3, $value));
        }

        return $validated;
    }
}
