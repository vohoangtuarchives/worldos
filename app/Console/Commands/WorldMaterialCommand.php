<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Material\Services\WorldMaterialTracker;
use App\Domains\World\Repositories\WorldRepository;
use App\Domains\Material\Repositories\MaterialRepository;
use Illuminate\Console\Command;

final class WorldMaterialCommand extends Command
{
    protected $signature = 'world:materials 
                            {--world-id= : Specific world ID to analyze}
                            {--track : Track and update material states}
                            {--statistics : Show material statistics}
                            {--optimize : Show optimization suggestions}
                            {--add= : Add material to world (format: material_id,quantity)}
                            {--remove= : Remove material instance from world}
                            {--transfer= : Transfer material between worlds (format: from_id,to_id,instance_id)}
                            {--degrade= : Degrade material instance}
                            {--upgrade= : Upgrade material instance}';

    protected $description = 'Manage and track world materials';

    public function __construct(
        private readonly WorldMaterialTracker $materialTracker,
        private readonly WorldRepository $worldRepository,
        private readonly MaterialRepository $materialRepository,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $worldId = $this->option('world-id');
        $track = $this->option('track');
        $showStatistics = $this->option('statistics');
        $showOptimize = $this->option('optimize');
        $addMaterial = $this->option('add');
        $removeInstance = $this->option('remove');
        $transferMaterial = $this->option('transfer');
        $degradeInstance = $this->option('degrade');
        $upgradeInstance = $this->option('upgrade');

        if ($worldId) {
            return $this->processWorld(
                $worldId,
                $track,
                $showStatistics,
                $showOptimize,
                $addMaterial,
                $removeInstance,
                $transferMaterial,
                $degradeInstance,
                $upgradeInstance
            );
        } else {
            return $this->processAllWorlds($track, $showStatistics);
        }
    }

    private function processWorld(
        string $worldId,
        bool $track,
        bool $showStatistics,
        bool $showOptimize,
        ?string $addMaterial,
        ?string $removeInstance,
        ?string $transferMaterial,
        ?string $degradeInstance,
        ?string $upgradeInstance
    ): int {
        
        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                $this->error("World {$worldId} not found");
                return self::FAILURE;
            }

            $this->info("📦 Material Management for World {$worldId}: {$world->name()}");

            // Process commands
            if ($addMaterial) {
                $this->addMaterial($world, $addMaterial);
            }

            if ($removeInstance) {
                $this->removeMaterial($world, $removeInstance);
            }

            if ($transferMaterial) {
                $this->transferMaterial($world, $transferMaterial);
            }

            if ($degradeInstance) {
                $this->degradeMaterial($world, $degradeInstance);
            }

            if ($upgradeInstance) {
                $this->upgradeMaterial($world, $upgradeInstance);
            }

            // Track materials
            if ($track) {
                $this->trackMaterials($world);
            }

            // Show statistics
            if ($showStatistics) {
                $this->showStatistics($world);
            }

            // Show optimization
            if ($showOptimize) {
                $this->showOptimization($world);
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Material management failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function processAllWorlds(bool $track, bool $showStatistics): int
    {
        $worlds = $this->worldRepository->findAll();

        if ($worlds->isEmpty()) {
            $this->info('No worlds found');
            return self::SUCCESS;
        }

        $this->info("📦 Material Analysis for All Worlds");
        $this->newLine();

        $summaryData = [];
        foreach ($worlds as $world) {
            if ($track) {
                $this->trackMaterials($world);
            }

            $stats = $this->materialTracker->getMaterialStatistics($world);
            
            $summaryData[] = [
                $world->id(),
                $world->name(),
                $stats['total_instances'],
                $stats['active_instances'],
                $stats['broken_instances'],
                $stats['average_durability'],
                $stats['scarcity_level'],
                $stats['abundance_level'],
            ];
        }

        $this->table(
            ['ID', 'Name', 'Total', 'Active', 'Broken', 'Avg Durability', 'Scarcity', 'Abundance'],
            $summaryData
        );

        if ($showStatistics) {
            $this->displayAggregateStatistics($worlds);
        }

        return self::SUCCESS;
    }

    private function trackMaterials($world): void
    {
        $this->line("🔍 Tracking materials for world {$world->id()}...");

        $collection = $this->materialTracker->trackWorldMaterials($world);

        $this->info("✅ Tracked {$collection->count()} material instances");
        $this->line("   Active: {$collection->getActive()->count()}");
        $this->line("   Broken: {$collection->getBroken()->count()}");
        $this->line("   Magical: {$collection->getMagical()->count()}");
        $this->line("   Average Durability: " . number_format($collection->getAverageDurability(), 1));
    }

    private function showStatistics($world): void
    {
        $stats = $this->materialTracker->getMaterialStatistics($world);

        $this->newLine();
        $this->info("📊 Material Statistics:");
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Instances', $stats['total_instances']],
                ['Active Instances', $stats['active_instances']],
                ['Retired Instances', $stats['retired_instances']],
                ['Broken Instances', $stats['broken_instances']],
                ['Average Durability', number_format($stats['average_durability'], 1)],
                ['Average Strength', number_format($stats['average_strength'], 1)],
                ['Total Value', number_format($stats['total_value'], 2)],
                ['Scarcity Level', $stats['scarcity_level']],
                ['Abundance Level', $stats['abundance_level']],
            ]
        );

        // Type breakdown
        $this->newLine();
        $this->info("📋 Material Types:");
        foreach ($stats['material_types'] as $type => $count) {
            $this->line("  {$type}: {$count}");
        }

        // Location breakdown
        $this->newLine();
        $this->info("📍 Location Distribution:");
        foreach ($stats['locations'] as $location => $count) {
            $this->line("  {$location}: {$count}");
        }

        // State breakdown
        $this->newLine();
        $this->info("🔄 State Distribution:");
        foreach ($stats['states'] as $state => $count) {
            $this->line("  {$state}: {$count}");
        }
    }

    private function showOptimization($world): void
    {
        $optimizations = $this->materialTracker->optimizeMaterialDistribution($world);

        $this->newLine();
        $this->info("🎯 Optimization Suggestions:");

        if (empty($optimizations)) {
            $this->line("  ✅ No optimizations needed - materials are optimally distributed");
            return;
        }

        foreach ($optimizations as $opt) {
            $priorityIcon = match ($opt['priority']) {
                'high' => '🔴',
                'medium' => '🟡',
                'low' => '🟢',
                default => '⚪'
            };

            $this->line("  {$priorityIcon} {$opt['type']}: {$opt['suggestion']}");
            $this->line("     Instance: {$opt['instance_id']}");
        }
    }

    private function addMaterial($world, string $addMaterial): void
    {
        $parts = explode(',', $addMaterial);
        if (count($parts) < 2) {
            $this->error("Invalid format. Use: material_id,quantity[,properties]");
            return;
        }

        $materialId = trim($parts[0]);
        $quantity = (int) trim($parts[1]);
        $properties = [];

        if (isset($parts[2])) {
            $propertyString = trim($parts[2]);
            parse_str($propertyString, $properties);
        }

        $material = $this->materialRepository->findById($materialId);
        if (!$material) {
            $this->error("Material {$materialId} not found");
            return;
        }

        $this->materialTracker->addMaterialToWorld($world, $material, $quantity, $properties);
        
        $this->info("✅ Added {$quantity} instances of material {$materialId} to world {$world->id()}");
    }

    private function removeMaterial($world, string $instanceId): void
    {
        $this->materialTracker->removeMaterialFromWorld($world, $instanceId);
        $this->info("✅ Removed material instance {$instanceId} from world {$world->id()}");
    }

    private function transferMaterial($world, string $transferMaterial): void
    {
        $parts = explode(',', $transferMaterial);
        if (count($parts) < 3) {
            $this->error("Invalid format. Use: from_world_id,to_world_id,instance_id");
            return;
        }

        $fromWorldId = trim($parts[0]);
        $toWorldId = trim($parts[1]);
        $instanceId = trim($parts[2]);

        if ($fromWorldId !== $world->id()) {
            $this->error("Source world ID {$fromWorldId} doesn't match current world {$world->id()}");
            return;
        }

        $toWorld = $this->worldRepository->findById($toWorldId);
        if (!$toWorld) {
            $this->error("Target world {$toWorldId} not found");
            return;
        }

        $this->materialTracker->transferMaterial($world, $toWorld, $instanceId);
        
        $this->info("✅ Transferred material instance {$instanceId} from world {$fromWorldId} to {$toWorldId}");
    }

    private function degradeMaterial($world, string $instanceId): void
    {
        $amount = $this->ask('Enter degradation amount (0.0-1.0):', '0.1');
        $amount = (float) $amount;

        $this->materialTracker->degradeMaterial($world, $instanceId, $amount);
        
        $this->info("✅ Degraded material instance {$instanceId} by {$amount}");
    }

    private function upgradeMaterial($world, string $instanceId): void
    {
        $this->line("Available upgrade types:");
        $this->line("  1. strength - Increase strength level");
        $this->line("  2. durability - Increase durability");
        $this->line("  3. purity - Increase purity");
        $this->line("  4. enchantment - Add magical properties");
        $this->line("  5. reinforcement - Remove fragile property");

        $upgradeType = $this->ask('Enter upgrade type:');
        $amount = $this->ask('Enter upgrade amount (0.0-1.0):', '0.1');
        $amount = (float) $amount;

        $upgrades = [
            ['type' => $upgradeType, 'amount' => $amount]
        ];

        $this->materialTracker->upgradeMaterial($world, $instanceId, $upgrades);
        
        $this->info("✅ Upgraded material instance {$instanceId} with {$upgradeType} (+{$amount})");
    }

    private function displayAggregateStatistics($worlds): void
    {
        $this->newLine();
        $this->info("🌍 Aggregate Material Statistics:");
        
        $totalWorlds = $worlds->count();
        $totalInstances = 0;
        $totalActive = 0;
        $totalBroken = 0;
        $totalValue = 0;

        foreach ($worlds as $world) {
            $stats = $this->materialTracker->getMaterialStatistics($world);
            $totalInstances += $stats['total_instances'];
            $totalActive += $stats['active_instances'];
            $totalBroken += $stats['broken_instances'];
            $totalValue += $stats['total_value'];
        }

        $avgInstancesPerWorld = $totalInstances / $totalWorlds;
        $avgActiveRate = $totalActive / max(1, $totalInstances) * 100;
        $avgBrokenRate = $totalBroken / max(1, $totalInstances) * 100;

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Worlds', $totalWorlds],
                ['Total Instances', $totalInstances],
                ['Average per World', number_format($avgInstancesPerWorld, 1)],
                ['Active Rate', number_format($avgActiveRate, 1) . '%'],
                ['Broken Rate', number_format($avgBrokenRate, 1) . '%'],
                ['Total Value', number_format($totalValue, 2)],
            ]
        );

        $this->newLine();
        $this->info("🎯 Global Recommendations:");
        
        if ($avgBrokenRate > 20) {
            $this->line("  ⚠️  High broken rate - consider better maintenance");
        }
        
        if ($avgActiveRate < 70) {
            $this->line("  📉 Low active rate - many materials need repair");
        }
        
        if ($avgInstancesPerWorld < 10) {
            $this->line("  📦 Low material count - worlds may need more resources");
        }
    }
}
