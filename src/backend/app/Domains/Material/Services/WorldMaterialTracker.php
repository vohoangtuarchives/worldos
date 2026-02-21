<?php

declare(strict_types=1);

namespace App\Domains\Material\Services;

use App\Domains\Material\Aggregates\MaterialAggregate;
use App\Domains\Material\ValueObjects\MaterialInstance;
use App\Domains\World\Aggregates\WorldAggregate;
use App\Domains\Material\Collections\WorldMaterialCollection;
use Tuzy\Domain\Material\ValueObject\MaterialState;
use App\Domains\Material\Contracts\MaterialRepositoryInterface;
use App\Domains\Material\Repositories\WorldMaterialRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class WorldMaterialTracker
{
    private const MATERIAL_DECAY_RATE = 0.01; // 1% decay per tick
    private const DEGRADATION_RATE = 0.02; // 2% degradation per use
    private const SCARCITY_THRESHOLD = 0.3; // Below this is scarce
    private const ABUNDANCE_THRESHOLD = 0.8; // Above this is abundant

    public function __construct(
        private readonly MaterialRepositoryInterface $materialRepository,
        private readonly WorldMaterialRepository $worldMaterialRepository,
    ) {}

    public function trackWorldMaterials(WorldAggregate $world): WorldMaterialCollection
    {
        $collection = new WorldMaterialCollection($world->id());

        // Get all materials in this world
        $materials = $this->materialRepository->findByWorldId($world->id());
        
        foreach ($materials as $material) {
            $instances = $this->worldMaterialRepository->findInstancesByMaterialId($world->id(), $material->id());
            
            foreach ($instances as $instance) {
                $collection->add($instance);
            }
        }

        // Update material states
        $this->updateMaterialStates($collection, $world);

        // Apply world effects
        $this->applyWorldEffects($collection, $world);

        // Record changes
        $this->recordMaterialChanges($collection, $world);

        Log::info('Materials tracked', [
            'world_id' => $world->id(),
            'total_materials' => $collection->count(),
            'active_instances' => $collection->getActive()->count(),
            'degraded_instances' => $collection->getDegraded()->count(),
        ]);

        return $collection;
    }

    public function addMaterialToWorld(
        WorldAggregate $world,
        MaterialAggregate $material,
        int $quantity = 1,
        array $properties = []
    ): void {
        
        for ($i = 0; $i < $quantity; $i++) {
            $instance = new MaterialInstance(
                id: uniqid('mat_inst_', true),
                materialId: $material->id(),
                worldId: $world->id(),
                strengthLevel: $properties['strength_level'] ?? $material->baseStrength(),
                durability: $properties['durability'] ?? 100.0,
                purity: $properties['purity'] ?? 1.0,
                location: $properties['location'] ?? 'unknown',
                owner: $properties['owner'] ?? null,
                createdAt: now(),
                lastUsedAt: null,
                retiredAt: null,
                metadata: $properties['metadata'] ?? []
            );

            $this->worldMaterialRepository->save($instance);
        }

        Log::info('Materials added to world', [
            'world_id' => $world->id(),
            'material_id' => $material->id(),
            'quantity' => $quantity,
        ]);
    }

    public function removeMaterialFromWorld(
        WorldAggregate $world,
        string $instanceId,
        string $reason = 'manual_removal'
    ): void {
        
        $instance = $this->worldMaterialRepository->findInstance($world->id(), $instanceId);
        
        if (!$instance) {
            throw new \InvalidArgumentException("Material instance {$instanceId} not found in world {$world->id()}");
        }

        $retiredInstance = $instance->retire($reason);
        $this->worldMaterialRepository->save($retiredInstance);

        Log::info('Material removed from world', [
            'world_id' => $world->id(),
            'instance_id' => $instanceId,
            'reason' => $reason,
        ]);
    }

    public function transferMaterial(
        WorldAggregate $fromWorld,
        WorldAggregate $toWorld,
        string $instanceId,
        ?string $newOwner = null
    ): void {
        
        $instance = $this->worldMaterialRepository->findInstance($fromWorld->id(), $instanceId);
        
        if (!$instance) {
            throw new \InvalidArgumentException("Material instance {$instanceId} not found in world {$fromWorld->id()}");
        }

        $transferredInstance = $instance->transferTo($toWorld->id(), $newOwner);
        $this->worldMaterialRepository->save($transferredInstance);

        Log::info('Material transferred between worlds', [
            'from_world' => $fromWorld->id(),
            'to_world' => $toWorld->id(),
            'instance_id' => $instanceId,
            'new_owner' => $newOwner,
        ]);
    }

    public function degradeMaterial(
        WorldAggregate $world,
        string $instanceId,
        float $degradationAmount = null
    ): void {
        
        $instance = $this->worldMaterialRepository->findInstance($world->id(), $instanceId);
        
        if (!$instance) {
            throw new \InvalidArgumentException("Material instance {$instanceId} not found in world {$world->id()}");
        }

        $degradationAmount = $degradationAmount ?? self::DEGRADATION_RATE;
        $degradedInstance = $instance->degrade($degradationAmount);
        
        $this->worldMaterialRepository->save($degradedInstance);

        Log::info('Material degraded', [
            'world_id' => $world->id(),
            'instance_id' => $instanceId,
            'degradation_amount' => $degradationAmount,
            'new_durability' => $degradedInstance->durability,
        ]);
    }

    public function upgradeMaterial(
        WorldAggregate $world,
        string $instanceId,
        array $upgrades
    ): void {
        
        $instance = $this->worldMaterialRepository->findInstance($world->id(), $instanceId);
        
        if (!$instance) {
            throw new \InvalidArgumentException("Material instance {$instanceId} not found in world {$world->id()}");
        }

        $upgradedInstance = $instance->upgrade($upgrades);
        $this->worldMaterialRepository->save($upgradedInstance);

        Log::info('Material upgraded', [
            'world_id' => $world->id(),
            'instance_id' => $instanceId,
            'upgrades' => $upgrades,
        ]);
    }

    private function updateMaterialStates(WorldMaterialCollection $collection, WorldAggregate $world): void
    {
        $entropy = $world->currentEntropy()->value();
        
        foreach ($collection->all() as $instance) {
            if ($instance->isRetired()) {
                continue;
            }

            // Apply entropy-based decay
            $decayAmount = self::MATERIAL_DECAY_RATE * (1 + $entropy);
            $instance = $instance->decay($decayAmount);

            // Apply time-based degradation
            if ($instance->isOld()) {
                $instance = $instance->degrade(self::DEGRADATION_RATE);
            }

            // Update state based on conditions
            $newState = $this->calculateMaterialState($instance, $world);
            $instance = $instance->updateState($newState);

            $collection->update($instance);
        }
    }

    private function applyWorldEffects(WorldMaterialCollection $collection, WorldAggregate $world): void
    {
        $entropy = $world->currentEntropy()->value();

        // High entropy affects materials
        if ($entropy > 0.7) {
            $this->applyHighEntropyEffects($collection, $world);
        }

        // Apply shock event effects
        $activeEvents = $this->getActiveShockEvents($world);
        foreach ($activeEvents as $event) {
            $this->applyShockEventEffects($collection, $event);
        }

        // Apply faction effects
        $factionEffects = $this->calculateFactionEffects($world);
        $this->applyFactionEffects($collection, $factionEffects);
    }

    private function applyHighEntropyEffects(WorldMaterialCollection $collection, WorldAggregate $world): void
    {
        // High entropy causes material degradation
        foreach ($collection->getActive() as $instance) {
            // Fragile materials degrade faster
            if ($instance->isFragile()) {
                $instance = $instance->degrade(0.05);
                $collection->update($instance);
            }

            // Magical materials become unstable
            if ($instance->isMagical()) {
                $instance = $instance->addInstability(0.1);
                $collection->update($instance);
            }
        }
    }

    private function applyShockEventEffects(WorldMaterialCollection $collection, $event): void
    {
        $effects = $this->getEventMaterialEffects($event);
        
        foreach ($collection->getActive() as $instance) {
            if ($this->isInstanceAffected($instance, $event)) {
                foreach ($effects as $effect) {
                    $instance = $this->applyEffectToInstance($instance, $effect);
                    $collection->update($instance);
                }
            }
        }
    }

    private function applyFactionEffects(WorldMaterialCollection $collection, array $factionEffects): void
    {
        foreach ($collection->getActive() as $instance) {
            if ($instance->owner && isset($factionEffects[$instance->owner])) {
                $effect = $factionEffects[$instance->owner];
                $instance = $this->applyEffectToInstance($instance, $effect);
                $collection->update($instance);
            }
        }
    }

    private function recordMaterialChanges(WorldMaterialCollection $collection, WorldAggregate $world): void
    {
        $changes = $collection->getChanges();
        
        foreach ($changes as $change) {
            $this->worldMaterialRepository->recordChange($world->id(), $change);
        }
    }

    private function calculateMaterialState(MaterialInstance $instance, WorldAggregate $world): MaterialState
    {
        $durabilityRatio = $instance->durability / 100.0;
        $entropy = $world->currentEntropy()->value();

        return match (true) {
            $instance->isRetired() => MaterialState::RETIRED,
            $instance->durability <= 0 => MaterialState::BROKEN,
            $durabilityRatio < 0.2 => MaterialState::DAMAGED,
            $durabilityRatio < 0.5 => MaterialState::WORN,
            $instance->isUnstable() => MaterialState::UNSTABLE,
            $entropy > 0.8 && $instance->isMagical() => MaterialState::CORRUPTED,
            default => MaterialState::STABLE
        };
    }

    private function isInstanceAffected(MaterialInstance $instance, $event): bool
    {
        // Check if instance is in affected region
        if ($instance->location === $event->affectedRegion()) {
            return true;
        }

        // Check if instance owner is affected
        if ($instance->owner && $this->isOwnerAffected($instance->owner, $event)) {
            return true;
        }

        // Check material type vulnerability
        return $this->isMaterialTypeVulnerable($instance, $event);
    }

    private function isOwnerAffected(string $owner, $event): bool
    {
        // Simplified - would check actual faction/character data
        return false;
    }

    private function isMaterialTypeVulnerable(MaterialInstance $instance, $event): bool
    {
        $vulnerabilities = [
            'plague' => ['organic', 'biological'],
            'civil_war' => ['infrastructure', 'buildings'],
            'magic_collapse' => ['magical', 'enchanted'],
            'famine' => ['food', 'organic'],
            'invasion' => ['infrastructure', 'defensive'],
            'earthquake' => ['buildings', 'structures'],
            'myth_awakening' => ['magical', 'sacred'],
        ];

        $eventVulnerabilities = $vulnerabilities[$event->type()] ?? [];
        
        return in_array($instance->materialType(), $eventVulnerabilities);
    }

    private function getEventMaterialEffects($event): array
    {
        return match ($event->type()) {
            'plague' => [
                ['type' => 'contamination', 'amount' => 0.3],
                ['type' => 'degradation', 'amount' => 0.2],
            ],
            'civil_war' => [
                ['type' => 'damage', 'amount' => 0.4],
                ['type' => 'destruction', 'amount' => 0.1],
            ],
            'magic_collapse' => [
                ['type' => 'instability', 'amount' => 0.5],
                ['type' => 'corruption', 'amount' => 0.3],
            ],
            'famine' => [
                ['type' => 'depletion', 'amount' => 0.6],
                ['type' => 'scarcity', 'amount' => 0.4],
            ],
            'invasion' => [
                ['type' => 'damage', 'amount' => 0.3],
                ['type' => 'theft', 'amount' => 0.2],
            ],
            'earthquake' => [
                ['type' => 'damage', 'amount' => 0.5],
                ['type' => 'destruction', 'amount' => 0.2],
            ],
            'myth_awakening' => [
                ['type' => 'enchantment', 'amount' => 0.4],
                ['type' => 'corruption', 'amount' => 0.1],
            ],
            default => []
        };
    }

    private function applyEffectToInstance(MaterialInstance $instance, array $effect): MaterialInstance
    {
        return match ($effect['type']) {
            'damage' => $instance->damage($effect['amount']),
            'degradation' => $instance->degrade($effect['amount']),
            'destruction' => $instance->destroy(),
            'contamination' => $instance->contaminate($effect['amount']),
            'instability' => $instance->addInstability($effect['amount']),
            'corruption' => $instance->corrupt($effect['amount']),
            'depletion' => $instance->deplete($effect['amount']),
            'theft' => $instance->steal(),
            'enchantment' => $instance->enchant($effect['amount']),
            default => $instance
        };
    }

    private function getActiveShockEvents(WorldAggregate $world): array
    {
        // Simplified - would fetch actual events
        return [];
    }

    private function calculateFactionEffects(WorldAggregate $world): array
    {
        // Simplified - would calculate based on faction power/stability
        return [];
    }

    public function getMaterialStatistics(WorldAggregate $world): array
    {
        $collection = $this->trackWorldMaterials($world);
        
        return [
            'total_instances' => $collection->count(),
            'active_instances' => $collection->getActive()->count(),
            'retired_instances' => $collection->getRetired()->count(),
            'broken_instances' => $collection->getBroken()->count(),
            'average_durability' => $collection->getAverageDurability(),
            'material_types' => $collection->getTypeBreakdown(),
            'locations' => $collection->getLocationBreakdown(),
            'owners' => $collection->getOwnerBreakdown(),
            'states' => $collection->getStateBreakdown(),
            'scarcity_level' => $this->calculateScarcityLevel($collection),
            'abundance_level' => $this->calculateAbundanceLevel($collection),
        ];
    }

    private function calculateScarcityLevel(WorldMaterialCollection $collection): string
    {
        $activeRatio = $collection->getActive()->count() / max(1, $collection->count());
        
        return match (true) {
            $activeRatio < self::SCARCITY_THRESHOLD => 'critical',
            $activeRatio < 0.5 => 'scarce',
            $activeRatio < 0.7 => 'limited',
            default => 'adequate'
        };
    }

    private function calculateAbundanceLevel(WorldMaterialCollection $collection): string
    {
        $activeRatio = $collection->getActive()->count() / max(1, $collection->count());
        
        return match (true) {
            $activeRatio > self::ABUNDANCE_THRESHOLD => 'abundant',
            $activeRatio > 0.6 => 'plentiful',
            $activeRatio > 0.4 => 'moderate',
            default => 'limited'
        };
    }

    public function optimizeMaterialDistribution(WorldAggregate $world): array
    {
        $collection = $this->trackWorldMaterials($world);
        $optimizations = [];

        // Identify underutilized materials
        $underutilized = $collection->getUnderutilized();
        foreach ($underutilized as $instance) {
            $optimizations[] = [
                'type' => 'redistribution',
                'instance_id' => $instance->id,
                'suggestion' => 'Move to location where material is needed',
                'priority' => 'medium'
            ];
        }

        // Identify materials needing repair
        $damaged = $collection->getDamaged();
        foreach ($damaged as $instance) {
            $optimizations[] = [
                'type' => 'repair',
                'instance_id' => $instance->id,
                'suggestion' => 'Repair before complete failure',
                'priority' => 'high'
            ];
        }

        // Identify redundant materials
        $redundant = $collection->getRedundant();
        foreach ($redundant as $instance) {
            $optimizations[] = [
                'type' => 'reallocation',
                'instance_id' => $instance->id,
                'suggestion' => 'Redistribute to scarce areas',
                'priority' => 'low'
            ];
        }

        return $optimizations;
    }
}
