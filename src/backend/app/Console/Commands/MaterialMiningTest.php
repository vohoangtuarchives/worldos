<?php

namespace App\Console\Commands;

use WorldOS\Legacy\Application\World\Interaction\WorldGraphManager;
use WorldOS\Legacy\Application\World\Interaction\InteractionZone;
use WorldOS\Legacy\Application\World\Interaction\MultiWorldCoordinator;
use WorldOS\Legacy\Application\World\Interaction\HybridPresetGenerator;
use WorldOS\Legacy\Application\World\Interaction\MaterialExtractor;
use WorldOS\Blueprint\Domain\Legacy\WorldState;
use Illuminate\Console\Command;

class MaterialMiningTest extends Command
{
    protected $signature = 'materials:test {--worlds=6 : Number of worlds to create} {--ticks=100 : Number of ticks to simulate} {--export : Export materials to database}';
    protected $description = 'Test material extraction from multi-world interactions';

    public function handle(
        WorldGraphManager $graphManager,
        MultiWorldCoordinator $coordinator
    ) {
        $this->info('⚗️ Material Mining Test');
        $this->info('========================');

        $worldCount = (int) $this->option('worlds');
        $tickCount = (int) $this->option('ticks');
        $export = $this->option('export');

        $this->info("Creating {$worldCount} worlds for material extraction...");
        $worlds = $this->createTestWorlds($worldCount);

        foreach ($worlds as $world) {
            $coordinator->addWorld($world);
        }

        $this->info("Starting {$tickCount} tick simulation with material extraction...");
        $this->newLine();

        $totalMaterials = 0;
        $materialTypes = [];

        // Run simulation
        for ($tick = 1; $tick <= $tickCount; $tick++) {
            $events = $coordinator->processWorldTick();
            
            // Count materials extracted this tick
            $materialArchive = $coordinator->getMaterialArchive();
            $currentMaterialCount = count($materialArchive->getMaterialsByType(''));
            $newMaterials = $currentMaterialCount - $totalMaterials;
            $totalMaterials = $currentMaterialCount;

            if ($newMaterials > 0 && $tick % 10 === 0) {
                $this->info("Tick {$tick}: +{$newMaterials} materials extracted (Total: {$totalMaterials})");
            }
        }

        // Display final material report
        $this->displayMaterialReport($coordinator->getMaterialReport());

        // Show material catalog
        $this->displayMaterialCatalog($coordinator->getMaterialArchive());

        // Show high-value materials
        $this->displayHighValueMaterials($coordinator->getMaterialArchive());

        // Show story combinations
        $this->displayStoryCombinations($coordinator->getMaterialArchive());

        // Export if requested
        if ($export) {
            $this->info('Exporting materials to database...');
            $coordinator->getMaterialArchive()->exportToDatabase();
            $this->info('✅ Materials exported successfully!');
        }

        $this->newLine();
        $this->info("✅ Material mining completed! Total materials extracted: {$totalMaterials}");
    }

    private function createTestWorlds(int $count): array
    {
        $worlds = [];
        $presets = ['faith', 'rational', 'political', 'resource', 'chaotic', 'stable'];
        
        for ($i = 0; $i < $count; $i++) {
            $preset = $presets[$i % count($presets)];
            
            $world = new WorldState([
                'id' => "world_{$i}",
                'currentPreset' => $preset,
                'coherence' => rand(40, 80) / 100,
                'entropy' => rand(10, 60) / 100,
                'stability' => rand(30, 70) / 100,
                'dominance_level' => rand(20, 80) / 100,
                'permeability' => rand(30, 90) / 100,
                'positionX' => rand(-100, 100),
                'positionY' => rand(-100, 100),
                'belief_mass' => rand(20, 80) / 100,
                'data_consistency' => rand(30, 90) / 100,
                'ritual_density' => rand(20, 70) / 100,
                'contradiction_index' => rand(10, 50) / 100,
                'propaganda_effort' => rand(20, 60) / 100,
                'war_probability' => rand(10, 40) / 100,
                'scarcity_rate' => rand(20, 80) / 100,
                'resource_flow' => rand(30, 70) / 100,
                'randomness' => rand(10, 90) / 100,
            ]);
            
            $worlds[] = $world;
        }

        return $worlds;
    }

    private function displayMaterialReport(array $report): void
    {
        $this->info('📊 Material Mining Report:');
        $this->info('========================');
        
        $this->line("Total Materials: {$report['summary']['total_materials']}");
        $this->line("High-Value Materials: {$report['summary']['high_value_count']}");
        $this->line("Average Tension: " . number_format($report['summary']['average_tension'], 3));
        
        $this->newLine();
        $this->info('🏆 Rarity Distribution:');
        foreach ($report['summary']['rarity_distribution'] as $rarity => $count) {
            $this->line("  {$rarity}: {$count}");
        }
        
        $this->newLine();
        $this->info('📚 Legendary Materials:');
        if (!empty($report['legendary_materials'])) {
            foreach ($report['legendary_materials'] as $material) {
                $this->line("  • {$material['type']}: {$material['archetype']} (Tension: " . number_format($material['tension_level'], 2) . ")");
            }
        } else {
            $this->line('  None found');
        }
        
        $this->newLine();
    }

    private function displayMaterialCatalog($materialArchive): void
    {
        $this->info('📦 Material Catalog by Type:');
        $this->info('============================');
        
        $stats = $materialArchive->getMaterialStatistics();
        
        foreach ($stats['type_distribution'] as $type => $count) {
            $materials = $materialArchive->getMaterialsByType($type);
            $avgTension = 0;
            $highestPotential = 0;
            
            if (!empty($materials)) {
                $avgTension = array_sum(array_column($materials, 'tension_level')) / count($materials);
                $highestPotential = max(array_column($materials, 'story_potential'));
            }
            
            $this->line("{$type}: {$count} materials (Avg Tension: " . number_format($avgTension, 2) . ", Max Potential: " . number_format($highestPotential, 2) . ")");
        }
        
        $this->newLine();
    }

    private function displayHighValueMaterials($materialArchive): void
    {
        $this->info('💎 Top 10 High-Value Materials:');
        $this->info('==============================');
        
        $highValueMaterials = $materialArchive->getHighValueMaterials(10);
        
        $this->table(
            ['Type', 'Archetype', 'Rarity', 'Tension', 'Story Potential'],
            array_map(fn($m) => [
                $m['type'],
                $m['archetype'],
                $m['rarity'],
                number_format($m['tension_level'], 2),
                number_format($m['story_potential'], 2)
            ], $highValueMaterials)
        );
        
        $this->newLine();
    }

    private function displayStoryCombinations($materialArchive): void
    {
        $this->info('🔗 Story Combinations:');
        $this->info('====================');
        
        $highValueMaterials = $materialArchive->getHighValueMaterials(20);
        $combinations = $materialArchive->findStoryCombinations($highValueMaterials);
        
        if (empty($combinations)) {
            $this->line('No compatible material combinations found.');
            return;
        }
        
        $this->info('Top 5 Story Combinations:');
        $this->table(
            ['Materials', 'Compatibility', 'Story Type', 'Potential', 'Themes'],
            array_map(fn($c) => [
                implode(' + ', array_map(fn($m) => explode('_', $m['type'])[0], $c['materials'])),
                number_format($c['compatibility'] * 100, 1) . '%',
                $c['story_type'],
                number_format($c['potential'], 2),
                implode(', ', array_slice($c['themes'], 0, 3))
            ], array_slice($combinations, 0, 5))
        );
        
        $this->newLine();
    }
}
