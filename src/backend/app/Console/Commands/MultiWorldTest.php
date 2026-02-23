<?php

namespace App\Console\Commands;

use WorldOS\Legacy\Application\World\Interaction\WorldGraphManager;
use WorldOS\Legacy\Application\World\Interaction\InteractionZone;
use WorldOS\Legacy\Application\World\Interaction\MultiWorldCoordinator;
use WorldOS\Legacy\Application\World\Interaction\HybridPresetGenerator;
use WorldOS\Blueprint\Domain\Legacy\WorldState;
use Illuminate\Console\Command;

class MultiWorldTest extends Command
{
    protected $signature = 'multiworld:test {--worlds=5 : Number of worlds to create} {--ticks=100 : Number of ticks to simulate}';
    protected $description = 'Test multi-world interaction system';

    public function handle(
        WorldGraphManager $graphManager,
        MultiWorldCoordinator $coordinator
    ) {
        $this->info('🌐 Multi-World Interaction Test');
        $this->info('================================');

        $worldCount = (int) $this->option('worlds');
        $tickCount = (int) $this->option('ticks');

        $this->info("Creating {$worldCount} worlds...");
        $worlds = $this->createTestWorlds($worldCount);

        foreach ($worlds as $world) {
            $coordinator->addWorld($world);
        }

        $this->info("Starting {$tickCount} tick simulation...");
        $this->newLine();

        // Initial state
        $this->displayWorldStates($worlds, 0);

        // Run simulation
        for ($tick = 1; $tick <= $tickCount; $tick++) {
            $events = $coordinator->processWorldTick();
            
            if ($tick % 20 === 0) {
                $this->displayTickSummary($tick, $events, $worlds);
            }
        }

        // Final state
        $this->newLine();
        $this->info('Final World States:');
        $this->displayWorldStates($worlds, $tickCount);

        // Display interaction zones
        $this->displayInteractionZones($coordinator->getInteractionZones());

        // Display hybrid candidates
        $this->displayHybridCandidates($worlds, $coordinator);

        $this->newLine();
        $this->info('✅ Multi-world test completed successfully!');
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
                'dominanceLevel' => rand(20, 80) / 100,
                'permeability' => rand(30, 90) / 100,
                'positionX' => rand(-100, 100),
                'positionY' => rand(-100, 100),
                'beliefMass' => rand(20, 80) / 100,
                'dataConsistency' => rand(30, 90) / 100,
                'ritualDensity' => rand(20, 70) / 100,
                'contradictionIndex' => rand(10, 50) / 100,
                'propagandaEffort' => rand(20, 60) / 100,
                'warProbability' => rand(10, 40) / 100,
                'scarcityRate' => rand(20, 80) / 100,
                'resourceFlow' => rand(30, 70) / 100,
                'randomness' => rand(10, 90) / 100,
                'rigidity' => rand(20, 80) / 100,
                'adaptability' => rand(20, 80) / 100,
            ]);
            
            $worlds[] = $world;
        }

        return $worlds;
    }

    private function displayWorldStates(array $worlds, int $tick): void
    {
        $this->info("Tick {$tick}:");
        $this->table(
            ['ID', 'Preset', 'Coherence', 'Entropy', 'Stability', 'Dominance'],
            array_map(fn($w) => [
                $w->id,
                $w->currentPreset,
                number_format($w->coherence, 3),
                number_format($w->entropy, 3),
                number_format($w->stability, 3),
                number_format($w->dominanceLevel, 3)
            ], $worlds)
        );
        $this->newLine();
    }

    private function displayTickSummary(int $tick, array $events, array $worlds): void
    {
        $this->info("Tick {$tick} Summary:");
        
        if (!empty($events)) {
            $this->info("📊 Events: " . count($events));
            foreach ($events as $event) {
                $this->line("  • {$event['type']}: {$event['description']}");
            }
        } else {
            $this->info("📊 Events: None");
        }

        $zones = count(array_filter(
            $this->getInteractionZones($worlds),
            fn($z) => $z->getWorldCount() >= 3
        ));
        
        if ($zones > 0) {
            $this->info("🌍 Interaction Zones: {$zones}");
        }
        
        $this->newLine();
    }

    private function displayInteractionZones(array $zones): void
    {
        if (empty($zones)) {
            $this->info("🌍 No interaction zones detected");
            return;
        }

        $this->info("🌍 Interaction Zones:");
        $this->table(
            ['Worlds', 'Coherence', 'Dominant Narratives', 'Interactions'],
            array_map(fn($z) => [
                $z->getWorldCount(),
                number_format($z->zone_coherence, 3),
                implode(', ', $z->dominant_narratives),
                $z->getInteractionCount()
            ], $zones)
        );
        $this->newLine();
    }

    private function displayHybridCandidates(array $worlds, MultiWorldCoordinator $coordinator): void
    {
        $this->info("🧬 Hybrid Candidates:");
        
        $hasCandidates = false;
        foreach ($worlds as $world) {
            $candidates = $coordinator->getWorldInteractionSummary($world->id)['hybrid_candidates'] ?? [];
            
            if (!empty($candidates)) {
                $hasCandidates = true;
                $this->info("World {$world->id} ({$world->currentPreset}):");
                foreach ($candidates as $candidate) {
                    $this->line("  • {$candidate['type']}: " . number_format($candidate['compatibility'] * 100, 1) . "% compatible");
                }
            }
        }

        if (!$hasCandidates) {
            $this->info("No hybrid candidates detected");
        }
        
        $this->newLine();
    }

    private function getInteractionZones(array $worlds): array
    {
        // Simple zone detection for testing
        $zones = [];
        $processed = [];
        
        foreach ($worlds as $world) {
            if (in_array($world->id, $processed)) continue;
            
            // Find nearby worlds
            $nearby = [];
            foreach ($worlds as $other) {
                if ($world->id === $other->id) continue;
                
                $distance = sqrt(
                    pow($world->positionX - $other->positionX, 2) + 
                    pow($world->positionY - $other->positionY, 2)
                );
                
                if ($distance < 50) { // Within interaction range
                    $nearby[] = $other;
                    $processed[] = $other->id;
                }
            }
            
            if (count($nearby) >= 2) {
                $zones[] = new InteractionZone(
                    worlds: array_merge([$world], $nearby),
                    zoneCoherence: array_sum(array_map(fn($w) => $w->coherence, array_merge([$world], $nearby))) / (count($nearby) + 1),
                    dominantNarratives: array_unique(array_map(fn($w) => $w->currentPreset, array_merge([$world], $nearby))),
                    activeInteractions: [] // Simplified for test
                );
            }
        }
        
        return $zones;
    }
}
