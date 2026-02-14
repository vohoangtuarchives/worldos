<?php

namespace App\Domains\Material\Mutation;

use App\Domains\Material\MaterialInstance;
use App\Domains\Material\Contracts\MaterialRepositoryInterface;

class MutationExecutor
{
    private MaterialRepositoryInterface $repository;

    public function __construct(MaterialRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Execute a mutation for a material instance.
     * 
     * @param MaterialInstance $instance
     * @param array $pathway Mutation pathway definition
     * @return MaterialInstance|null New mutated instance or null if target doesn't exist
     */
    public function executeMutation(MaterialInstance $instance, array $pathway): ?MaterialInstance
    {
        $targetCode = $pathway['target_code'];
        $strengthTransfer = $pathway['strength_transfer'];

        // Find target material
        $targetMaterial = $this->repository->findByCode($targetCode);
        
        if (!$targetMaterial) {
            // Target material doesn't exist yet - would need to be created
            // For now, skip mutation
            return null;
        }

        // Calculate transferred strength
        $transferredStrength = (int)($instance->strength_level * $strengthTransfer);

        // Create new mutated instance
        $mutatedInstance = $this->repository->createInstance($targetMaterial, $instance->world_id, [
            'strength_level' => $transferredStrength,
            'activation_epoch' => $instance->world->tick ?? 0,
            'mutation_state' => [
                'mutated_from' => $instance->material->code,
                'mutation_pathway' => $pathway['description'],
                'mutation_epoch' => $instance->world->tick ?? 0
            ]
        ]);

        // Mark original as mutated
        $instance->mutation_state = array_merge($instance->mutation_state ?? [], [
            'mutated_to' => $targetCode,
            'mutation_epoch' => $instance->world->tick ?? 0,
            'pathway_description' => $pathway['description']
        ]);
        
        // Reduce original strength
        $instance->strength_level = (int)($instance->strength_level * (1 - $strengthTransfer));
        
        $this->repository->updateInstance($instance, [
            'mutation_state' => $instance->mutation_state,
            'strength_level' => $instance->strength_level
        ]);

        return $mutatedInstance;
    }

    /**
     * Execute multiple mutations.
     * 
     * @param array $mutations [instance_id => pathway]
     * @return array Created mutated instances
     */
    public function executeMutations(array $mutations): array
    {
        $mutatedInstances = [];

        foreach ($mutations as $instanceId => $pathway) {
            $instance = $this->repository->findInstance($instanceId);
            
            if (!$instance) {
                continue;
            }

            $mutated = $this->executeMutation($instance, $pathway);
            
            if ($mutated) {
                $mutatedInstances[] = $mutated;
            }
        }

        return $mutatedInstances;
    }
}
