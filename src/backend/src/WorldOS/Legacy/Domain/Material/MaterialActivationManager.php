<?php

namespace WorldOS\Legacy\Domain\Material;

use WorldOS\Legacy\Domain\Material\Contracts\MaterialRepositoryInterface;
use WorldOS\Legacy\Domain\CognitiveKernel\ArchetypePool;
use App\Models\World;

class MaterialActivationManager
{
    private MaterialRepositoryInterface $repository;
    private ArchetypePool $archetypePool;
    private MaterialArchetypeAffinity $affinity;

    public function __construct(
        MaterialRepositoryInterface $repository,
        ArchetypePool $archetypePool,
        MaterialArchetypeAffinity $affinity
    ) {
        $this->repository = $repository;
        $this->archetypePool = $archetypePool;
        $this->affinity = $affinity;
    }

    /**
     * Check and activate dormant materials based on world conditions.
     */
    public function checkActivations(World $world): array
    {
        $instances = $this->repository->getInstancesForWorld($world->id);
        $dormantInstances = $instances->filter(fn($i) => $i->activation_epoch === null);
        
        $activated = [];
        $worldContext = $this->buildWorldContext($world);

        foreach ($dormantInstances as $instance) {
            if ($this->shouldActivate($instance, $worldContext)) {
                $this->activateMaterial($instance, $world->tick);
                $activated[] = $instance;
            }
        }

        return $activated;
    }

    /**
     * Determine if a dormant material should activate.
     */
    private function shouldActivate(MaterialInstance $instance, array $context): bool
    {
        $material = $instance->material;
        $preconditions = $material->preconditions ?? [];

        // Check if preconditions are met
        foreach ($preconditions as $condition) {
            if (!$this->evaluateCondition($condition, $context)) {
                return false;
            }
        }

        // Check archetype thresholds from affinity matrix
        $affinityData = $this->affinity->getAffinity($material->code);
        if ($affinityData && isset($affinityData['activation_threshold'])) {
            $threshold = $affinityData['activation_threshold'];
            
            // Check if any related archetype exceeds threshold
            foreach ($affinityData['archetypes'] ?? [] as $archetypeKey) {
                if (($context[$archetypeKey] ?? 0) >= $threshold) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Activate a material instance.
     */
    private function activateMaterial(MaterialInstance $instance, int $epoch): void
    {
        $this->repository->updateInstance($instance, [
            'activation_epoch' => $epoch,
            'strength_level' => max(1, $instance->strength_level),
        ]);
    }

    /**
     * Check and deactivate materials based on decay or conditions.
     */
    public function checkDeactivations(World $world): array
    {
        $instances = $this->repository->getInstancesForWorld($world->id);
        $activeInstances = $instances->filter(fn($i) => $i->activation_epoch !== null && !$i->retired_at);
        
        $deactivated = [];
        $worldContext = $this->buildWorldContext($world);

        foreach ($activeInstances as $instance) {
            if ($this->shouldDeactivate($instance, $worldContext)) {
                $this->deactivateMaterial($instance, $world->tick);
                $deactivated[] = $instance;
            }
        }

        return $deactivated;
    }

    /**
     * Determine if an active material should deactivate.
     */
    private function shouldDeactivate(MaterialInstance $instance, array $context): bool
    {
        // Deactivate if strength drops to 0
        if ($instance->strength_level <= 0) {
            return true;
        }

        // Check if incompatible materials are too strong
        $material = $instance->material;
        $incompatible = $material->incompatible_with ?? [];

        foreach ($incompatible as $incompatibleCode) {
            $incompatibleInstance = $this->repository->getInstancesForWorld($instance->world_id)
                ->first(fn($i) => $i->material->code === $incompatibleCode && $i->activation_epoch !== null);

            if ($incompatibleInstance && $incompatibleInstance->strength_level > $instance->strength_level * 2) {
                return true; // Overwhelmed by incompatible material
            }
        }

        return false;
    }

    /**
     * Deactivate (retire) a material instance.
     */
    private function deactivateMaterial(MaterialInstance $instance, int $epoch): void
    {
        $this->repository->updateInstance($instance, [
            'retired_at' => now(),
            'mutation_state' => array_merge($instance->mutation_state ?? [], [
                'retirement_epoch' => $epoch,
                'retirement_reason' => 'Deactivated due to low strength or incompatibility',
            ]),
        ]);
    }

    /**
     * Build world context from archetype weights.
     */
    private function buildWorldContext(World $world): array
    {
        $weights = $this->archetypePool->getWeightsForWorld($world);
        
        $context = [];
        foreach ($weights as $weight) {
            $context[$weight->archetype_key] = $weight->weight;
        }

        // Add World Stage to context for preconditions
        $currentStageKey = $world->config['current_stage'] ?? 'mundane';
        $registry = app(\WorldOS\Legacy\Domain\Power\PowerStageRegistry::class);
        $stageInfo = $registry->getStageAndConstraint($currentStageKey);
        
        if ($stageInfo && isset($stageInfo['stage'])) {
            $context['stage'] = $stageInfo['stage']->level();
        } else {
            $context['stage'] = 0;
        }

        return $context;
    }

    /**
     * Evaluate a condition string against context.
     */
    private function evaluateCondition(string $condition, array $context): bool
    {
        // Simple condition evaluation (e.g., "sacred > 0.5")
        if (preg_match('/(\w+)\s*([<>]=?)\s*([\d.]+)/', $condition, $matches)) {
            $key = $matches[1];
            $operator = $matches[2];
            $threshold = (float)$matches[3];
            
            $value = $context[$key] ?? 0;
            
            return match($operator) {
                '>' => $value > $threshold,
                '>=' => $value >= $threshold,
                '<' => $value < $threshold,
                '<=' => $value <= $threshold,
                default => false
            };
        }
        
        return true; // If can't parse, assume true
    }
}
