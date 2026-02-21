<?php

namespace Tuzy\Application\World\Interaction;

use Tuzy\Domain\World\WorldState;
use Tuzy\Application\World\Interaction\WorldGraphManager;
use Tuzy\Application\World\Interaction\HybridPresetGenerator;
use Tuzy\Application\World\Interaction\Presets\PresetInteraction;
use Tuzy\Application\World\Interaction\MaterialExtractor;
use Tuzy\Application\World\Interaction\MaterialArchive;

class MultiWorldCoordinator
{
    private WorldGraphManager $graphManager;
    private HybridPresetGenerator $hybridGenerator;
    private MaterialExtractor $materialExtractor;
    private MaterialArchive $materialArchive;
    private array $interactionHistory = [];
    private array $pendingHybrids = [];

    public function __construct(
        WorldGraphManager $graphManager,
        HybridPresetGenerator $hybridGenerator,
        MaterialExtractor $materialExtractor,
        MaterialArchive $materialArchive
    ) {
        $this->graphManager = $graphManager;
        $this->hybridGenerator = $hybridGenerator;
        $this->materialExtractor = $materialExtractor;
        $this->materialArchive = $materialArchive;
    }

    public function processWorldTick(): array
    {
        $events = [];
        
        // Update all interactions
        $this->graphManager->updateAllInteractions();
        
        // Extract materials from interactions
        foreach ($this->graphManager->getInteractions() as $interactionData) {
            // Convert array to WorldInteraction object
            $interaction = new \App\Models\World\WorldInteraction([
                'world_a_id' => $interactionData['world_a'],
                'world_b_id' => $interactionData['world_b'],
                'interaction_type' => $interactionData['type'],
                'strength' => $interactionData['strength'],
                'active_from_tick' => time(),
                'metadata' => []
            ]);
            
            $materials = $this->materialExtractor->extractFromInteraction($interaction);
            foreach ($materials as $material) {
                $this->materialArchive->addMaterial($material);
            }
        }
        
        // Process interaction zones
        $zones = $this->graphManager->detectEmergentClusters();
        foreach ($zones as $zone) {
            $zoneMaterials = $this->materialExtractor->extractFromZone($zone);
            foreach ($zoneMaterials as $material) {
                $this->materialArchive->addMaterial($material);
            }
            
            $events = array_merge($events, $this->processInteractionZone($zone));
        }
        
        // Process hybrid generation
        $events = array_merge($events, $this->processHybridGeneration());
        
        // Extract materials from world evolution
        foreach ($this->graphManager->getWorlds() as $world) {
            $worldMaterials = $this->materialExtractor->extractFromWorldEvolution($world, $this->getPreviousWorldState($world->id));
            foreach ($worldMaterials as $material) {
                $this->materialArchive->addMaterial($material);
            }
        }
        
        // Update interaction history
        $this->updateInteractionHistory();
        
        return $events;
    }

    public function addWorld(WorldState $world): void
    {
        $this->graphManager->addWorld($world);
    }

    public function removeWorld(string $worldId): void
    {
        $this->graphManager->removeWorld($worldId);
        $this->cleanupWorldData($worldId);
    }

    public function getInteractionZones(): array
    {
        return $this->graphManager->detectEmergentClusters();
    }

    public function getWorldInteractionSummary(string $worldId): array
    {
        $worlds = $this->graphManager->getWorlds();
        $world = $worlds[$worldId] ?? null;
        
        if (!$world) {
            return [];
        }
        
        $interactions = array_filter(
            $this->graphManager->getInteractions(),
            fn($interaction) => 
                $interaction['world_a'] === $worldId || 
                $interaction['world_b'] === $worldId
        );
        
        $summary = [
            'world_id' => $worldId,
            'total_interactions' => count($interactions),
            'strongest_interaction' => $this->getStrongestInteraction($interactions),
            'dominant_interaction_type' => $this->getDominantInteractionType($interactions),
            'interaction_strength_sum' => array_sum(array_column($interactions, 'strength')),
            'hybrid_candidates' => $this->hybridGenerator->getHybridCandidates($world)
        ];
        
        return $summary;
    }

    public function simulateMultiWorldEvolution(int $ticks): array
    {
        $evolutionHistory = [];
        
        for ($tick = 0; $tick < $ticks; $tick++) {
            $events = $this->processWorldTick();
            $zones = $this->getInteractionZones();
            
            $evolutionHistory[] = [
                'tick' => $tick,
                'events' => $events,
                'zones' => array_map(fn($z) => [
                    'world_count' => $z->getWorldCount(),
                    'coherence' => $z->zone_coherence,
                    'dominant_narratives' => $z->dominant_narratives
                ], $zones),
                'world_states' => $this->captureWorldStates()
            ];
            
            // Apply evolution to worlds
            $this->applyEvolutionToAllWorlds();
        }
        
        return $evolutionHistory;
    }

    private function processInteractionZone(InteractionZone $zone): array
    {
        $events = [];
        
        // Calculate zone evolution
        $evolution = $zone->calculateZoneEvolution();
        $this->applyZoneEvolution($zone, $evolution);
        
        // Check for zone collapse
        if ($zone->detectZoneCollapse()) {
            $events[] = [
                'type' => 'ZONE_COLLAPSE',
                'zone_worlds' => $zone->getWorldIds(),
                'severity' => 1.0 - $zone->zone_coherence,
                'description' => 'Interaction zone has collapsed'
            ];
        }
        
        // Spawn zone events
        $events = array_merge($events, $zone->spawnZoneEvents());
        
        return $events;
    }

    private function processHybridGeneration(): array
    {
        $events = [];
        $worlds = $this->graphManager->getWorlds();
        
        foreach ($this->graphManager->getInteractions() as $interaction) {
            if ($interaction['strength'] < 0.7) continue;
            
            $worldA = $worlds[$interaction['world_a']];
            $worldB = $worlds[$interaction['world_b']];
            
            if ($this->hybridGenerator->canCreateHybrid($worldA, $worldB)) {
                $hybridKey = $worldA->id . '-' . $worldB->id;
                
                if (!isset($this->pendingHybrids[$hybridKey])) {
                    $this->pendingHybrids[$hybridKey] = [
                        'world_a' => $worldA->id,
                        'world_b' => $worldB->id,
                        'strength' => $interaction['strength'],
                        'type' => $interaction['type'],
                        'created_at' => time()
                    ];
                    
                    $events[] = [
                        'type' => 'HYBRID_POTENTIAL',
                        'worlds' => [$worldA->id, $worldB->id],
                        'strength' => $interaction['strength'],
                        'description' => 'Hybrid preset potential detected'
                    ];
                }
            }
        }
        
        return $events;
    }

    private function applyZoneEvolution(InteractionZone $zone, array $evolution): void
    {
        foreach ($zone->worlds as $world) {
            $world->coherence += $evolution['coherence_delta'] ?? 0;
            $world->entropy += $evolution['entropy_delta'] ?? 0;
            $world->stability += $evolution['stability_shift'] ?? 0;
            
            // Apply belief mutations
            foreach ($evolution['belief_mutation'] ?? [] as $mutation) {
                if (in_array($world->id, $mutation['worlds'])) {
                    $world->applyBeliefMutation($mutation);
                }
            }
            
            // Apply resource exchange
            foreach ($evolution['resource_exchange'] ?? [] as $exchange) {
                $world->applyResourceExchange($exchange);
            }
        }
    }

    private function applyEvolutionToAllWorlds(): void
    {
        $worlds = $this->graphManager->getWorlds();
        
        foreach ($worlds as $world) {
            $world->evolve();
        }
    }

    private function updateInteractionHistory(): void
    {
        $interactions = $this->graphManager->getInteractions();
        
        foreach ($interactions as $interaction) {
            $key = $interaction['world_a'] . '-' . $interaction['world_b'];
            
            if (!isset($this->interactionHistory[$key])) {
                $this->interactionHistory[$key] = [
                    'world_a' => $interaction['world_a'],
                    'world_b' => $interaction['world_b'],
                    'type' => $interaction['type'],
                    'strength_history' => [],
                    'total_interactions' => 0
                ];
            }
            
            $this->interactionHistory[$key]['strength_history'][] = $interaction['strength'];
            $this->interactionHistory[$key]['total_interactions']++;
        }
    }

    private function cleanupWorldData(string $worldId): void
    {
        // Remove from interaction history
        $this->interactionHistory = array_filter(
            $this->interactionHistory,
            fn($interaction) => 
                $interaction['world_a'] !== $worldId && 
                $interaction['world_b'] !== $worldId
        );
        
        // Remove from pending hybrids
        foreach ($this->pendingHybrids as $key => $hybrid) {
            if ($hybrid['world_a'] === $worldId || $hybrid['world_b'] === $worldId) {
                unset($this->pendingHybrids[$key]);
            }
        }
    }

    private function getStrongestInteraction(array $interactions): array
    {
        if (empty($interactions)) return [];
        
        usort($interactions, fn($a, $b) => $b['strength'] <=> $a['strength']);
        return $interactions[0];
    }

    private function getDominantInteractionType(array $interactions): string
    {
        if (empty($interactions)) return 'NONE';
        
        $typeCounts = [];
        foreach ($interactions as $interaction) {
            $type = $interaction['type'];
            $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
        }
        
        arsort($typeCounts);
        return array_key_first($typeCounts);
    }

    private function captureWorldStates(): array
    {
        $worlds = $this->graphManager->getWorlds();
        $states = [];
        
        foreach ($worlds as $world) {
            $states[$world->id] = [
                'preset' => $world->currentPreset,
                'coherence' => $world->coherence,
                'entropy' => $world->entropy,
                'stability' => $world->stability,
                'dominance' => $world->dominanceLevel,
                'permeability' => $world->permeability
            ];
        }
        
        return $states;
    }

    public function getInteractionHistory(): array
    {
        return $this->interactionHistory;
    }

    public function getPendingHybrids(): array
    {
        return $this->pendingHybrids;
    }

    public function getMaterialArchive(): MaterialArchive
    {
        return $this->materialArchive;
    }

    public function getMaterialReport(): array
    {
        return $this->materialArchive->generateMaterialReport();
    }

    private function getPreviousWorldState(string $worldId): array
    {
        // Simple implementation - in production, this would query database
        return [
            'coherence' => 0.5,
            'entropy' => 0.3,
            'stability' => 0.5,
            'dominance_level' => 0.5
        ];
    }
}
