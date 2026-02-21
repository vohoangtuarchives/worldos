<?php

namespace Tuzy\Application\Cosmology\Agents;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Application\Cosmology\Mathematics\Vector;

class InfluenceField
{
    private Vector $vector;

    public function __construct(Vector $vector)
    {
        $this->vector = $vector;
    }

    public static function create(array $dimensions): self
    {
        return new self(new Vector($dimensions));
    }

    public function getVector(): Vector
    {
        return $this->vector;
    }

    public function apply(WorldStateVector $state): WorldStateVector
    {
        // Add influence to state
        // In reality, this might be a more complex field interaction
        // For now, it's linear superposition
        $newState = $state->add($this->vector);
        
        // Return as WorldStateVector. 
        // Note: The add result is Vector, need to cast back.
        // We might want `add` in WorldStateVector to return WorldStateVector.
        // For now, reconstruct.
        // Also clamp values to valid range? Maybe not here, let Universe/Kernel handle clamping.
        
        return WorldStateVector::create(
             $newState->get(WorldStateVector::DIMENSION_ORDER),
             $newState->get(WorldStateVector::DIMENSION_ENTROPY),
             $newState->get(WorldStateVector::DIMENSION_COHESION),
             $newState->get(WorldStateVector::DIMENSION_LEGITIMACY),
             $newState->get(WorldStateVector::DIMENSION_INNOVATION),
             $newState->get(WorldStateVector::DIMENSION_MILITARY)
        );
    }
}
