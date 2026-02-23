<?php

namespace WorldOS\Legacy\Application\Material\State;

/**
 * SagaLens - Read WorldState Through Different Lenses
 * 
 * Saga = way to READ state, NOT modify it.
 * Same state → different saga views.
 */
class SagaLens
{
    /**
     * Read state through a specific saga lens.
     * 
     * @param WorldState $worldState
     * @param string $sagaType 'structural' | 'symbolic' | 'interaction' | 'full'
     * @return array Filtered state view
     */
    public function read(WorldState $worldState, string $sagaType): array
    {
        return match($sagaType) {
            'structural' => $this->structuralLens($worldState),
            'symbolic' => $this->symbolicLens($worldState),
            'interaction' => $this->interactionLens($worldState),
            'full' => $this->fullLens($worldState),
            default => throw new \InvalidArgumentException("Unknown saga type: {$sagaType}"),
        };
    }

    /**
     * Structural Saga - Focus on power, economy, infrastructure.
     */
    private function structuralLens(WorldState $state): array
    {
        return [
            'saga_type' => 'structural',
            'epoch' => $state->epoch,
            'core' => $state->core->toArray(),
            'structural' => $state->structural->toArray(),
            'meta' => [
                'epoch' => $state->meta->epoch,
                'collapse_proximity' => $state->meta->collapseProximity,
                'simulation_health' => $state->meta->simulationHealth,
            ],
        ];
    }

    /**
     * Symbolic Saga - Focus on belief, identity, memory.
     */
    private function symbolicLens(WorldState $state): array
    {
        return [
            'saga_type' => 'symbolic',
            'epoch' => $state->epoch,
            'symbolic' => $state->symbolic->toArray(),
            'memory' => $state->memory->toArray(),
            'meta' => [
                'epoch' => $state->meta->epoch,
                'entropy' => $state->meta->entropy,
                'drift_rate' => $state->meta->driftRate,
            ],
        ];
    }

    /**
     * Interaction Saga - Focus on external relations.
     */
    private function interactionLens(WorldState $state): array
    {
        return [
            'saga_type' => 'interaction',
            'epoch' => $state->epoch,
            'interaction' => $state->interaction->toArray(),
            'core' => [
                'population' => $state->core->population,
                'subsistence_base' => $state->core->subsistenceBase,
            ],
            'meta' => [
                'epoch' => $state->meta->epoch,
                'world_stability' => 1.0 - $state->meta->collapseProximity,
            ],
        ];
    }

    /**
     * Full Lens - All state components.
     */
    private function fullLens(WorldState $state): array
    {
        return [
            'saga_type' => 'full',
            'epoch' => $state->epoch,
            'core' => $state->core->toArray(),
            'structural' => $state->structural->toArray(),
            'symbolic' => $state->symbolic->toArray(),
            'memory' => $state->memory->toArray(),
            'interaction' => $state->interaction->toArray(),
            'meta' => $state->meta->toArray(),
        ];
    }
}
