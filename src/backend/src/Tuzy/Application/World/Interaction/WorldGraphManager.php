<?php

namespace Tuzy\Application\World\Interaction;

use Tuzy\Domain\World\WorldState;
use Tuzy\Application\World\Interaction\InteractionZone;
use Tuzy\Application\World\Interaction\Presets\PresetInteraction;

class WorldGraphManager
{
    private array $worlds = [];
    private array $interactions = [];
    private array $interactionZones = [];

    public function addWorld(WorldState $world): void
    {
        $this->worlds[$world->id] = $world;
        $this->calculateInteractionsForWorld($world);
    }

    public function removeWorld(string $worldId): void
    {
        unset($this->worlds[$worldId]);
        $this->removeInteractionsForWorld($worldId);
    }

    public function calculateInteractionStrength(WorldState $a, WorldState $b): float
    {
        $distance = $this->calculateDistance($a, $b);
        $coherenceDiff = abs($a->coherence - $b->coherence);
        
        $strength = 
            ($a->dominanceLevel * $b->permeability) +
            ($b->dominanceLevel * $a->permeability) -
            ($distance * 0.1) -
            ($coherenceDiff * 0.3);

        return max(0, min(1, $strength));
    }

    public function updateAllInteractions(): void
    {
        $this->interactions = [];
        
        foreach ($this->worlds as $worldA) {
            foreach ($this->worlds as $worldB) {
                if ($worldA->id >= $worldB->id) continue;
                
                $strength = $this->calculateInteractionStrength($worldA, $worldB);
                
                if ($strength > 0.1) { // Minimum threshold
                    $this->interactions[] = [
                        'world_a' => $worldA->id,
                        'world_b' => $worldB->id,
                        'strength' => $strength,
                        'type' => $this->determineInteractionType($worldA, $worldB, $strength)
                    ];
                }
            }
        }
    }

    public function detectEmergentClusters(): array
    {
        $clusters = [];
        $visited = [];

        foreach ($this->worlds as $world) {
            if (in_array($world->id, $visited)) continue;

            $cluster = $this->findConnectedWorlds($world, $visited);
            if (count($cluster) >= 3) {
                $clusters[] = new InteractionZone(
                    worlds: $cluster,
                    zone_coherence: $this->calculateZoneCoherence($cluster),
                    dominant_narratives: $this->extractDominantNarratives($cluster),
                    active_interactions: $this->getZoneInteractions($cluster)
                );
            }
        }

        return $clusters;
    }

    public function getWorlds(): array
    {
        return $this->worlds;
    }

    public function getInteractions(): array
    {
        return $this->interactions;
    }

    public function getInteractionZones(): array
    {
        return $this->interactionZones;
    }

    private function calculateInteractionsForWorld(WorldState $world): void
    {
        foreach ($this->worlds as $otherWorld) {
            if ($world->id === $otherWorld->id) continue;

            $strength = $this->calculateInteractionStrength($world, $otherWorld);
            
            if ($strength > 0.1) {
                $this->interactions[] = [
                    'world_a' => $world->id,
                    'world_b' => $otherWorld->id,
                    'strength' => $strength,
                    'type' => $this->determineInteractionType($world, $otherWorld, $strength)
                ];
            }
        }
    }

    private function removeInteractionsForWorld(string $worldId): void
    {
        $this->interactions = array_filter(
            $this->interactions,
            fn($interaction) => 
                $interaction['world_a'] !== $worldId && 
                $interaction['world_b'] !== $worldId
        );
    }

    private function calculateDistance(WorldState $a, WorldState $b): float
    {
        // Simple Euclidean distance in narrative space
        $dx = $a->positionX - $b->positionX;
        $dy = $a->positionY - $b->positionY;
        return sqrt($dx * $dx + $dy * $dy);
    }

    private function determineInteractionType(WorldState $a, WorldState $b, float $strength): string
    {
        // Determine interaction type based on preset types and strength
        $presetA = $a->currentPreset;
        $presetB = $b->currentPreset;

        if ($strength > 0.8) {
            return 'REALITY_DISTORTION';
        }

        if ($presetA === 'faith' && $presetB === 'rational') {
            return 'BELIEF_CONTAMINATION';
        }

        if ($presetA === 'political' || $presetB === 'political') {
            return 'NARRATIVE_BLEED';
        }

        if ($presetA === 'resource' || $presetB === 'resource') {
            return 'RESOURCE_CROSSFLOW';
        }

        return 'NARRATIVE_BLEED';
    }

    private function findConnectedWorlds(WorldState $start, array &$visited): array
    {
        $cluster = [];
        $queue = [$start];

        while (!empty($queue)) {
            $current = array_shift($queue);
            
            if (in_array($current->id, $visited)) continue;
            
            $visited[] = $current->id;
            $cluster[] = $current;

            // Find connected worlds
            foreach ($this->interactions as $interaction) {
                if ($interaction['strength'] < 0.5) continue; // Strong connections only

                $connectedId = null;
                if ($interaction['world_a'] === $current->id) {
                    $connectedId = $interaction['world_b'];
                } elseif ($interaction['world_b'] === $current->id) {
                    $connectedId = $interaction['world_a'];
                }

                if ($connectedId && !in_array($connectedId, $visited)) {
                    $queue[] = $this->worlds[$connectedId];
                }
            }
        }

        return $cluster;
    }

    private function calculateZoneCoherence(array $worlds): float
    {
        if (empty($worlds)) return 0;

        $totalCoherence = array_sum(array_map(fn($w) => $w->coherence, $worlds));
        return $totalCoherence / count($worlds);
    }

    private function extractDominantNarratives(array $worlds): array
    {
        $narratives = [];
        foreach ($worlds as $world) {
            $narratives[$world->currentPreset] = ($narratives[$world->currentPreset] ?? 0) + 1;
        }

        arsort($narratives);
        return array_keys($narratives);
    }

    private function getZoneInteractions(array $worlds): array
    {
        $worldIds = array_map(fn($w) => $w->id, $worlds);
        
        return array_filter(
            $this->interactions,
            fn($interaction) => 
                in_array($interaction['world_a'], $worldIds) &&
                in_array($interaction['world_b'], $worldIds)
        );
    }
}
