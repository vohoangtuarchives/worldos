<?php

namespace Tuzy\Application\Cosmology\Agents;

use Tuzy\Application\Cosmology\Entities\Universe;
use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Illuminate\Support\Str;

class Observer
{
    private string $id;
    private string $name;
    private float $observationStrength = 0.05; // Quantum collapse factor

    public function __construct(string $name, ?string $id = null)
    {
        $this->id = $id ?? (string) Str::uuid();
        $this->name = $name;
    }

    public function observe(Universe $universe): WorldStateVector
    {
        // Get current state
        $state = $universe->getState();
        
        // Quantum Effect: Observation reduces Entropy slightly (Collapse of wave function)
        // But increases Order or Innovation depending on observer type?
        // Let's say basic observation stabilizes the universe (reduces Entropy).
        
        $deltaEntropy = -1 * ($state->getEntropy() * $this->observationStrength);
        $deltaOrder = ($state->getEntropy() * $this->observationStrength * 0.5); // Some energy goes to Order
        
        $modification = WorldStateVector::create(
            $deltaOrder, // Order
            $deltaEntropy, // Entropy
            0, // Cohesion
            0, // Legitimacy
            0, // Innovation
            0 // Military
        );
        
        // Apply modification to Universe immediately?
        // Or just return what is seen? 
        // "Quantum-like observation effects" -> Change the observed system.
        $universe->applyInfluence($modification);
        
        return $universe->getState();
    }
}
