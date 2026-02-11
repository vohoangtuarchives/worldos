<?php

namespace App\Domains\Material;

use App\Domains\CognitiveKernel\ArchetypePool;
use App\Domains\Material\Contracts\MaterialRepositoryInterface;
use App\Models\World;

class MaterialArchetypeCoupler
{
    private MaterialArchetypeAffinity $affinity;
    private ArchetypePool $archetypePool;
    private MaterialRepositoryInterface $repository;

    public function __construct(
        MaterialArchetypeAffinity $affinity,
        ArchetypePool $archetypePool,
        MaterialRepositoryInterface $repository
    ) {
        $this->affinity = $affinity;
        $this->archetypePool = $archetypePool;
        $this->repository = $repository;
    }

    /**
     * Apply material influence on archetype drift.
     * 
     * @param World $world
     * @return array Drift deltas to apply
     */
    public function applyMaterialInfluence(World $world): array
    {
        $instances = $this->repository->getInstancesForWorld($world->id);
        $driftDeltas = [];

        foreach ($instances as $instance) {
            // Skip retired materials
            if ($instance->retired_at) {
                continue;
            }

            $material = $instance->material;
            $affinities = $this->affinity->getAffinities($material->code);

            if (!$affinities) {
                continue;
            }

            $driftModifier = $affinities['drift_modifier'];
            $influencedArchetypes = $affinities['archetypes'];

            // Apply drift to related archetypes based on material strength
            $strengthFactor = $instance->strength_level / 10; // Normalize to 0-1

            foreach ($influencedArchetypes as $archetypeKey) {
                if (!isset($driftDeltas[$archetypeKey])) {
                    $driftDeltas[$archetypeKey] = 0;
                }

                $driftDeltas[$archetypeKey] += $driftModifier * $strengthFactor;
            }
        }

        return $driftDeltas;
    }

    /**
     * Check if high archetype weights should activate dormant materials.
     * 
     * @param World $world
     * @return array Material instance IDs that were activated
     */
    public function checkArchetypeActivation(World $world): array
    {
        $archetypeWeights = $this->archetypePool->getWeightsForWorld($world)
            ->pluck('weight', 'archetype_key')
            ->toArray();

        $instances = $this->repository->getInstancesForWorld($world->id);
        $activated = [];

        foreach ($instances as $instance) {
            // Skip already active or retired materials
            if ($instance->activation_epoch !== null || $instance->retired_at) {
                continue;
            }

            $material = $instance->material;

            // NEW: Check Stage Requirement from preconditions
            $currentStageKey = $world->config['current_stage'] ?? 'mundane';
            $registry = app(\App\Domains\Power\PowerStageRegistry::class);
            $stageInfo = $registry->getStageAndConstraint($currentStageKey);
            $currentStageLevel = $stageInfo && isset($stageInfo['stage']) ? $stageInfo['stage']->level() : 0;

            $preconditions = $material->preconditions ?? [];
            foreach ($preconditions as $condition) {
                if (preg_match('/stage\s*([<>]=?)\s*([\d.]+)/', $condition, $matches)) {
                    $operator = $matches[1];
                    $threshold = (float)$matches[2];
                    $met = match($operator) {
                        '>' => $currentStageLevel > $threshold,
                        '>=' => $currentStageLevel >= $threshold,
                        '<' => $currentStageLevel < $threshold,
                        '<=' => $currentStageLevel <= $threshold,
                        default => true
                    };
                    if (!$met) continue 2; // Fail this material activation
                }
            }

            // Check if archetype weights are high enough to activate
            if ($this->affinity->canActivate($material->code, $archetypeWeights)) {
                $instance->activation_epoch = $world->tick;
                $this->repository->updateInstance($instance, ['activation_epoch' => $world->tick]);
                $activated[] = $instance->id;
            }
        }

        return $activated;
    }
}
