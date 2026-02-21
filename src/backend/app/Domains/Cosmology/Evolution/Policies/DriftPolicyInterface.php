<?php

namespace App\Domains\Cosmology\Evolution\Policies;

use App\Domains\Cosmology\Entities\WorldStateVector;
use Tuzy\Domain\Cosmology\ValueObject\WorldSeed;

interface DriftPolicyInterface
{
    /**
     * Determine how the universe's parameters (Ontology, Epistemic, Energy, Civilization)
     * drift given the current active conditions and the baseline archetype seed.
     * 
     * @param WorldSeed $seed The foundational properties and limits.
     * @param WorldStateVector $currentState Output of previous drifts.
     * @return array The calculated drift modifications to apply for the next epoch/tick.
     */
    public function calculateDrift(WorldSeed $seed, WorldStateVector $currentState): array;
}
