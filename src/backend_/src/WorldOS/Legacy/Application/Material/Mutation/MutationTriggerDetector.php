<?php

namespace WorldOS\Legacy\Application\Material\Mutation;

use WorldOS\Legacy\Domain\Material\MaterialInstance;
use WorldOS\Legacy\Domain\Material\Contracts\MaterialRepositoryInterface;
use WorldOS\Legacy\Domain\CognitiveKernel\ArchetypePool;
use App\Models\World;

class MutationTriggerDetector
{
    private MutationPathway $pathways;
    private ArchetypePool $archetypePool;
    private MaterialRepositoryInterface $repository;

    public function __construct(
        MutationPathway $pathways,
        ArchetypePool $archetypePool,
        MaterialRepositoryInterface $repository
    ) {
        $this->pathways = $pathways;
        $this->archetypePool = $archetypePool;
        $this->repository = $repository;
    }

    /**
     * Detect materials ready to mutate in a world.
     * 
     * @return array [instance_id => pathway]
     */
    public function detectMutations(World $world): array
    {
        $instances = $this->repository->getInstancesForWorld($world->id);
        $worldContext = $this->buildWorldContext($world);
        $mutations = [];

        foreach ($instances as $instance) {
            // Skip retired or already mutated materials
            if ($instance->retired_at || isset($instance->mutation_state['mutated_to'])) {
                continue;
            }

            $material = $instance->material;
            $pathway = $this->pathways->findMatchingPathway($material->code, $worldContext);

            if ($pathway) {
                $mutations[$instance->id] = $pathway;
            }
        }

        return $mutations;
    }

    /**
     * Build world context from archetype weights and world state.
     */
    private function buildWorldContext(World $world): array
    {
        $weights = $this->archetypePool->getWeightsForWorld($world);
        
        $context = [];
        foreach ($weights as $weight) {
            $context[$weight->archetype_key] = $weight->weight;
        }

        // Add world-level metrics
        $context['time_depth'] = $world->tick / 10; // Normalize tick to epochs
        $context['state_weakness'] = 1 - ($context['order'] ?? 0.5);
        
        // Placeholder for additional metrics
        $context['corruption'] = 0.3; // Would come from world state
        $context['anxiety'] = 0.4;
        $context['wealth_concentration'] = 0.5;
        $context['exclusivity'] = 0.4;
        $context['honor'] = 0.6;
        $context['autonomy'] = 0.5;

        return $context;
    }
}
