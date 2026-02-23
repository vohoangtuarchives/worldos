<?php

namespace App\Domains\Material;

use App\Domains\Material\Contracts\MaterialRepositoryInterface;
use App\Models\World;

class MaterialSeeder
{
    private MaterialRepositoryInterface $repository;

    public function __construct(MaterialRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Seed materials into a world based on archetype configuration.
     * 
     * @param World $world
     * @param array $archetypeWeights Archetype key => weight mapping
     * @return array Created MaterialInstance IDs
     */
    public function seedWorld(World $world, array $archetypeWeights = []): array
    {
        $instances = [];

        // Get all materials
        $materials = $this->repository->getAll();

        foreach ($materials as $material) {
            // Determine if this material should be seeded based on preconditions
            if ($this->shouldSeed($material, $archetypeWeights)) {
                $initialStrength = $this->calculateInitialStrength($material, $archetypeWeights);
                
                $instance = $this->repository->createInstance($material, $world->id, [
                    'strength_level' => $initialStrength,
                    'activation_epoch' => 0,
                    'mutation_state' => []
                ]);

                $instances[] = $instance->id;
            }
        }

        return $instances;
    }

    /**
     * Determine if a material should be seeded based on preconditions.
     */
    private function shouldSeed(Material $material, array $archetypeWeights): bool
    {
        // For now, seed all materials with default_lifecycle = 'active'
        // In future, check preconditions against world state
        return $material->default_lifecycle === \WorldOS\Legacy\Domain\Material\Enums\MaterialLifecycle::ACTIVE;
    }

    /**
     * Calculate initial strength based on archetype affinity.
     */
    private function calculateInitialStrength(Material $material, array $archetypeWeights): int
    {
        // Base strength
        $strength = 5;

        // TODO: Implement affinity-based calculation when MaterialArchetypeAffinity is ready
        // For now, return base strength
        return $strength;
    }
}
