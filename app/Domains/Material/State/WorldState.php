<?php

namespace App\Domains\Material\State;

/**
 * WorldState - Historical Accumulation Vector
 * 
 * This is NOT a snapshot. This is accumulated history.
 * 
 * Principles:
 * - State never resets
 * - All values 0.0 - 1.0 (normalized)
 * - Changes only via delta
 * - Saga = lens to read state
 */
class WorldState
{
    public function __construct(
        public readonly int $worldId,
        public readonly int $epoch,
        public readonly CoreState $core,
        public readonly StructuralState $structural,
        public readonly SymbolicState $symbolic,
        public readonly MemoryState $memory,
        public readonly InteractionState $interaction,
        public readonly MetaState $meta
    ) {}

    /**
     * Create initial world state (neutral).
     */
    public static function createInitial(int $worldId): self
    {
        return new self(
            worldId: $worldId,
            epoch: 0,
            core: CoreState::createNeutral(),
            structural: StructuralState::createNeutral(),
            symbolic: SymbolicState::createNeutral(),
            memory: MemoryState::createNeutral(),
            interaction: InteractionState::createNeutral(),
            meta: MetaState::createInitial()
        );
    }

    /**
     * Convert to array for persistence.
     */
    public function toArray(): array
    {
        return [
            'world_id' => $this->worldId,
            'epoch' => $this->epoch,
            'core_state' => $this->core->toArray(),
            'structural_state' => $this->structural->toArray(),
            'symbolic_state' => $this->symbolic->toArray(),
            'memory_state' => $this->memory->toArray(),
            'interaction_state' => $this->interaction->toArray(),
            'meta_state' => $this->meta->toArray(),
        ];
    }

    /**
     * Create from array (for reconstruction).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            worldId: $data['world_id'],
            epoch: $data['epoch'],
            core: CoreState::fromArray($data['core_state']),
            structural: StructuralState::fromArray($data['structural_state']),
            symbolic: SymbolicState::fromArray($data['symbolic_state']),
            memory: MemoryState::fromArray($data['memory_state']),
            interaction: InteractionState::fromArray($data['interaction_state']),
            meta: MetaState::fromArray($data['meta_state'])
        );
    }

    /**
     * Create new state with updated epoch.
     */
    public function withEpoch(int $epoch): self
    {
        return new self(
            worldId: $this->worldId,
            epoch: $epoch,
            core: $this->core,
            structural: $this->structural,
            symbolic: $this->symbolic,
            memory: $this->memory,
            interaction: $this->interaction,
            meta: $this->meta->withEpoch($epoch)
        );
    }

    /**
     * Create new state with updated components.
     */
    public function withComponents(
        ?CoreState $core = null,
        ?StructuralState $structural = null,
        ?SymbolicState $symbolic = null,
        ?MemoryState $memory = null,
        ?InteractionState $interaction = null,
        ?MetaState $meta = null
    ): self {
        return new self(
            worldId: $this->worldId,
            epoch: $this->epoch,
            core: $core ?? $this->core,
            structural: $structural ?? $this->structural,
            symbolic: $symbolic ?? $this->symbolic,
            memory: $memory ?? $this->memory,
            interaction: $interaction ?? $this->interaction,
            meta: $meta ?? $this->meta
        );
    }
}
