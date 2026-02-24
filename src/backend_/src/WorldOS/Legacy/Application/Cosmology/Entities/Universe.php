<?php

namespace WorldOS\Legacy\Application\Cosmology\Entities;

use WorldOS\Legacy\Application\Cosmology\Services\BasePhysicsEngine;
use Illuminate\Support\Str;

class Universe
{
    private string $id;
    private WorldStateVector $state;
    private array $parameters;
    private int $age = 0;
    private ?array $coords = null;
    private ?int $cosmicFactionId = null;

    public function __construct(WorldStateVector $initialState, array $parameters = [], ?string $id = null, int $age = 0, ?array $coords = null, ?int $cosmicFactionId = null)
    {
        $this->id = $id ?? (string) Str::uuid();
        $this->state = $initialState;
        $this->parameters = $parameters;
        $this->age = $age;
        $this->coords = $coords;
        $this->cosmicFactionId = $cosmicFactionId;
    }

    public function getCosmicFactionId(): ?int
    {
        return $this->cosmicFactionId;
    }

    public function setCosmicFactionId(?int $id): void
    {
        $this->cosmicFactionId = $id;
    }

    public function getHistory(): array
    {
        // Narrative history not yet implemented in entity, return empty for now
        return [];
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getState(): WorldStateVector
    {
        return $this->state;
    }

    public function setState(WorldStateVector $state): void
    {
        $this->state = $state;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function setAge(int $age): void
    {
        $this->age = $age;
    }

    public function getCoords(): ?array
    {
        return $this->coords;
    }

    public function evolve(BasePhysicsEngine $kernel): void
    {
        $this->state = $kernel->evolve($this->state);
        $this->age++;
    }

    public function applyInfluence(WorldStateVector $influenceVector): void
    {
        // Apply generic influence (from other universes or agents)
        // This is a simplified addition for now
        $newComponents = $this->state->add($influenceVector);
        
        $this->state = WorldStateVector::create(
             $newComponents->get(WorldStateVector::DIMENSION_ORDER),
             $newComponents->get(WorldStateVector::DIMENSION_ENTROPY),
             $newComponents->get(WorldStateVector::DIMENSION_COHESION),
             $newComponents->get(WorldStateVector::DIMENSION_LEGITIMACY),
             $newComponents->get(WorldStateVector::DIMENSION_INNOVATION),
             $newComponents->get(WorldStateVector::DIMENSION_MILITARY)
        );
    }

    /**
     * Apply story mutation delta (single boundary: only Mutation layer calls this).
     * state_new = state + delta, each dimension clamped to [0, 1].
     */
    public function applyMutation(WorldStateVector $delta): void
    {
        $newState = $this->state->add($delta);
        $clamped = $newState->clamp(0.0, 1.0);
        $components = $clamped->getAll();
        $this->state = WorldStateVector::fromArray($components);
    }

    public function applyAgentInfluence(\WorldOS\Legacy\Application\Cosmology\Agents\TranscendentAgent $agent): void
    {
        // Calculate the vector impact of the agent
        // For now, assume agent influence field is direct
        // Later can add distance/resonance factors
        $field = $agent->getInfluenceField()->getVector();
        
        // Convert generic Vector to WorldStateVector or compatible array logic
        // InfluenceField::apply returns WorldStateVector directly based on implementation plan Step 586?
        // Let's check Step 586. 
        // InfluenceField -> apply($state) returns WorldStateVector.
        // So we can use that directly.
        
        // But Universe holds the state.
        
        $this->state = $agent->getInfluenceField()->apply($this->state);
    }
}
