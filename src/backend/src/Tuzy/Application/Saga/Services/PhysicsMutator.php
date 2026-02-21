<?php

namespace Tuzy\Application\Saga\Services;

use Tuzy\Domain\World\ValueObject\PhysicsProfile;
use App\Models\World;

class PhysicsMutator
{
    /**
     * Apply physics drift to a world based on exposure to a target profile.
     * 
     * @param World $world The world to mutate
     * @param PhysicsProfile $targetProfile The profile to drift towards (e.g. Void)
     * @param float $exposure The intensity of exposure (0.0 - 1.0)
     * @return World The mutated world
     */
    public function drift(World $world, PhysicsProfile $targetProfile, float $exposure): World
    {
        $currentProfile = $world->physics_profile;
        $permeability = $currentProfile->dimensional_permeability;
        
        // Calculate actual drift amount based on world's permeability
        // High permeability = faster mutation
        $driftAmount = $exposure * ($permeability / 10.0);
        
        // Clamp drift
        $driftAmount = max(0.0, min(1.0, $driftAmount));

        $newProfile = $currentProfile->drift($targetProfile, $driftAmount);
        
        $world->physics_profile = $newProfile;
        $world->save();

        return $world;
    }

    /**
     * Calculate the "Corruption Level" of a world compared to a baseline.
     * 
     * @return float 0.0 (Pure) to 1.0 (Fully Corrupted)
     */
    public function calculateCorruptionIndex(World $world, PhysicsProfile $baseline): float
    {
        $current = $world->physics_profile;
        
        // Simple Euclidean distance approximation for now
        $diff = abs($current->instability_rate - $baseline->instability_rate)
              + abs($current->decay_rate - $baseline->decay_rate)
              + abs($current->mutation_chance - $baseline->mutation_chance);
              
        return min(1.0, $diff / 5.0); // Normalize arbitrary scale
    }
}
