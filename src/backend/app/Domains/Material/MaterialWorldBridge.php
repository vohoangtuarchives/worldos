<?php

namespace App\Domains\Material;

use App\Domains\Material\Engine\MaterialLawEngine;
use App\Domains\Material\Mutation\MutationTriggerDetector;
use App\Domains\Material\Mutation\MutationExecutor;
use App\Domains\Material\MaterialActivationManager;
use App\Domains\Material\Engine\CrossDomainPressureCalculator;
use App\Domains\Material\Contracts\MaterialRepositoryInterface;
use App\Models\World;

class MaterialWorldBridge
{
    private MaterialLawEngine $engine;
    private MaterialRepositoryInterface $repository;
    private MutationTriggerDetector $mutationDetector;
    private MutationExecutor $mutationExecutor;
    private MaterialActivationManager $activationManager;
    private CrossDomainPressureCalculator $pressureCalculator;

    public function __construct(
        MaterialLawEngine $engine,
        MaterialRepositoryInterface $repository,
        MutationTriggerDetector $mutationDetector,
        MutationExecutor $mutationExecutor,
        MaterialActivationManager $activationManager,
        CrossDomainPressureCalculator $pressureCalculator
    ) {
        $this->engine = $engine;
        $this->repository = $repository;
        $this->mutationDetector = $mutationDetector;
        $this->mutationExecutor = $mutationExecutor;
        $this->activationManager = $activationManager;
        $this->pressureCalculator = $pressureCalculator;
    }

    /**
     * Process all material instances for a world and apply effects to world state.
     * 
     * @param World $world
     * @param float $deltaTime Time passed in this tick (in years)
     * @return array Aggregated effects to apply to world
     */
    public function processTick(World $world, float $deltaTime): array
    {
        $instances = $this->repository->getInstancesForWorld($world->id);
        
        // Context now includes deltaTime for physics calculations
        $worldContext = [
            'delta_time' => $deltaTime,
            'tech_level' => 2, // Placeholder
        ];

        $aggregatedEffects = [
            'cohesion_modifier' => 0,
            'entropy_modifier' => 0,
            'fracture_risk' => 0,
            'maintenance_cost' => 0,
            'collapsed_materials' => [],
            'traces' => []
        ];

        foreach ($instances as $instance) {
            // Skip retired materials
            if ($instance->retired_at) {
                continue;
            }

            // Process tick for this material
            $result = $this->engine->tick($instance, $worldContext);

            // Aggregate effects
            if (isset($result['effects'])) {
                foreach ($result['effects'] as $key => $value) {
                    if (isset($aggregatedEffects[$key])) {
                        $aggregatedEffects[$key] += $value;
                    }
                }
            }

            // Track collapses
            if ($result['collapsed']) {
                $aggregatedEffects['collapsed_materials'][] = $result['material_code'];
            }

            // Collect traces
            if (!empty($result['traces'])) {
                $aggregatedEffects['traces'] = array_merge(
                    $aggregatedEffects['traces'],
                    $result['traces']
                );
            }
        }

        // Check for activations
        $activated = $this->activationManager->checkActivations($world);
        if (!empty($activated)) {
            $aggregatedEffects['activations'] = [
                'count' => count($activated),
                'materials' => array_map(fn($i) => $i->material->code, $activated)
            ];
        }

        // Check for deactivations
        $deactivated = $this->activationManager->checkDeactivations($world);
        if (!empty($deactivated)) {
            $aggregatedEffects['deactivations'] = [
                'count' => count($deactivated),
                'materials' => array_map(fn($i) => $i->material->code, $deactivated)
            ];
        }

        // Check for mutations
        $mutations = $this->mutationDetector->detectMutations($world);
        if (!empty($mutations)) {
            $mutatedInstances = $this->mutationExecutor->executeMutations($mutations);
            $aggregatedEffects['mutations'] = [
                'count' => count($mutatedInstances),
                'instances' => array_map(fn($i) => [
                    'from' => $i->mutation_state['mutated_from'] ?? null,
                    'to' => $i->material->code,
                    'description' => $i->mutation_state['mutation_pathway'] ?? null
                ], $mutatedInstances)
            ];
        }

        // Calculate and apply cross-domain pressures
        $instances = $this->repository->getInstancesForWorld($world->id);
        $pressures = $this->pressureCalculator->calculate($instances);
        if (!empty($pressures)) {
            $pressureEffects = $this->pressureCalculator->applyPressures($pressures, $instances);
            $aggregatedEffects['cross_domain_pressures'] = [
                'count' => count($pressureEffects),
                'effects' => $pressureEffects
            ];
        }

        return $aggregatedEffects;
    }
}
