<?php

namespace App\StoryEngine;

use App\Services\WorldLawProfileCache;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldLawProfile;
use App\Exceptions\Simulation\SimulationException;

class StateManager
{
    private WorldLawProfileCache $profileCache;
    private array $factionCache = [];
    private array $worldStateCache = [];

    public function __construct(WorldLawProfileCache $profileCache)
    {
        $this->profileCache = $profileCache;
    }

    /**
     * Initialize world state with factions and other components.
     */
    public function initializeWorldState(WorldState $world): WorldState
    {
        $this->initializeFactions($world);
        $this->initializeDefaultSeeds($world);
        
        return $world;
    }

    /**
     * Preload world law profile for better performance.
     */
    public function preloadWorldLawProfile(WorldState $world): ?WorldLawProfile
    {
        $worldId = $world->id ?? null;
        
        if ($worldId) {
            return $this->profileCache->getProfile($worldId);
        } else {
            return $this->profileCache->getDefaultProfile();
        }
    }

    /**
     * Initialize factions for the world state.
     */
    protected function initializeFactions(WorldState $world): void
    {
        $worldId = $world->id ?? null;

        if ($worldId) {
            $this->loadFactionsFromDatabase($worldId, $world);
        } else {
            $this->useDefaultFactions($world);
        }
    }

    /**
     * Load factions from database with caching.
     */
    protected function loadFactionsFromDatabase(string $worldId, WorldState $world): void
    {
        // Check cache first
        if (isset($this->factionCache[$worldId])) {
            $world->factions = $this->factionCache[$worldId];
            return;
        }

        try {
            // Optimized query with eager loading
            $dbFactions = \App\Models\Faction::where('world_id', $worldId)
                ->with(['relations', 'attributes'])
                ->get();

            $factionStates = [];
            foreach ($dbFactions as $faction) {
                $state = $this->createFactionStateFromModel($faction);
                $factionStates[] = $state;
            }

            // Cache the result
            $this->factionCache[$worldId] = $factionStates;
            $world->factions = $factionStates;

        } catch (\Exception $e) {
            throw SimulationException::stateCorruption(
                "Failed to load factions for world {$worldId}: " . $e->getMessage(),
                ['world_id' => $worldId, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Use default factions for testing/legacy scenarios.
     */
    protected function useDefaultFactions(WorldState $world): void
    {
        $world->factions = [
            new FactionState('sect_1', 'Azure Cloud Sect', 'Sect'),
            new FactionState('clan_1', 'Iron Blood Clan', 'Clan'),
            new FactionState('guild_1', 'Golden Pavilion', 'Guild'),
        ];
    }

    /**
     * Create faction state from database model.
     */
    protected function createFactionStateFromModel(\App\Models\Faction $faction): FactionState
    {
        $state = new FactionState((string)$faction->id, $faction->name, $faction->type);
        
        // Hydrate attributes efficiently
        if ($faction->attributes) {
            $this->hydrateFactionState($state, $faction->attributes);
        }

        return $state;
    }

    /**
     * Hydrate faction state from database attributes.
     */
    protected function hydrateFactionState(FactionState $state, array $attributes): void
    {
        if (isset($attributes['cohesion'])) {
            $state->cohesion = $attributes['cohesion'];
        }
        
        if (isset($attributes['economy'])) {
            $state->economy = $attributes['economy'];
        }
        
        if (isset($attributes['military_power'])) {
            $state->militaryPower = $attributes['military_power'];
        }

        if (isset($attributes['diplomacy'])) {
            $state->diplomacy = $attributes['diplomacy'];
        }

        if (isset($attributes['culture'])) {
            $state->culture = $attributes['culture'];
        }
    }

    /**
     * Initialize default seeds for the world.
     */
    protected function initializeDefaultSeeds(WorldState $world): void
    {
        // Default seeds can be added here if needed
        // For now, seeds are managed by the orchestrator
    }

    /**
     * Create a snapshot of the current world state.
     */
    public function createSnapshot(WorldState $world, int $tick): array
    {
        return [
            'timeline_id' => $world->id ?? 'unknown',
            'tick' => $tick,
            'world_state' => [
                'public_awareness' => $world->publicAwareness,
                'power_centers' => $world->powerCenters,
                'tier_index' => $world->tierIndex,
                'faction_count' => count($world->factions),
                'factions' => $this->serializeFactions($world->factions),
            ],
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Restore world state from snapshot.
     */
    public function restoreFromSnapshot(array $snapshot): WorldState
    {
        $world = new WorldState();
        
        if (isset($snapshot['world_state'])) {
            $stateData = $snapshot['world_state'];
            
            $world->publicAwareness = $stateData['public_awareness'] ?? 5;
            $world->powerCenters = $stateData['power_centers'] ?? 2;
            $world->tierIndex = $stateData['tier_index'] ?? 0;
            
            if (isset($stateData['factions'])) {
                $world->factions = $this->deserializeFactions($stateData['factions']);
            }
        }

        return $world;
    }

    /**
     * Serialize factions for snapshot storage.
     */
    protected function serializeFactions(array $factions): array
    {
        $serialized = [];
        
        foreach ($factions as $faction) {
            $serialized[] = [
                'id' => $faction->id,
                'name' => $faction->name,
                'type' => $faction->type,
                'cohesion' => $faction->cohesion ?? 50,
                'economy' => $faction->economy ?? null,
                'military_power' => $faction->militaryPower ?? null,
                'diplomacy' => $faction->diplomacy ?? null,
                'culture' => $faction->culture ?? null,
            ];
        }
        
        return $serialized;
    }

    /**
     * Deserialize factions from snapshot data.
     */
    protected function deserializeFactions(array $serializedFactions): array
    {
        $factions = [];
        
        foreach ($serializedFactions as $data) {
            $faction = new FactionState($data['id'], $data['name'], $data['type']);
            
            if (isset($data['cohesion'])) {
                $faction->cohesion = $data['cohesion'];
            }
            
            if (isset($data['economy'])) {
                $faction->economy = $data['economy'];
            }
            
            if (isset($data['military_power'])) {
                $faction->militaryPower = $data['militaryPower'];
            }
            
            if (isset($data['diplomacy'])) {
                $faction->diplomacy = $data['diplomacy'];
            }
            
            if (isset($data['culture'])) {
                $faction->culture = $data['culture'];
            }
            
            $factions[] = $faction;
        }
        
        return $factions;
    }

    /**
     * Cache world state for performance.
     */
    public function cacheWorldState(string $key, WorldState $world, int $ttl = 3600): void
    {
        $this->worldStateCache[$key] = [
            'state' => $world,
            'expires_at' => time() + $ttl,
        ];
    }

    /**
     * Get cached world state.
     */
    public function getCachedWorldState(string $key): ?WorldState
    {
        if (!isset($this->worldStateCache[$key])) {
            return null;
        }

        $cached = $this->worldStateCache[$key];
        
        if ($cached['expires_at'] < time()) {
            unset($this->worldStateCache[$key]);
            return null;
        }

        return $cached['state'];
    }

    /**
     * Clear expired cache entries.
     */
    public function clearExpiredCache(): void
    {
        $currentTime = time();
        
        foreach ($this->worldStateCache as $key => $cached) {
            if ($cached['expires_at'] < $currentTime) {
                unset($this->worldStateCache[$key]);
            }
        }
    }

    /**
     * Clear all caches.
     */
    public function clearAllCaches(): void
    {
        $this->factionCache = [];
        $this->worldStateCache = [];
    }

    /**
     * Get cache statistics.
     */
    public function getCacheStats(): array
    {
        $this->clearExpiredCache();
        
        return [
            'faction_cache_size' => count($this->factionCache),
            'world_state_cache_size' => count($this->worldStateCache),
            'total_cached_items' => count($this->factionCache) + count($this->worldStateCache),
        ];
    }

    /**
     * Validate world state integrity.
     */
    public function validateWorldState(WorldState $world): array
    {
        $issues = [];
        
        // Check for valid public awareness range
        if ($world->publicAwareness < 0 || $world->publicAwareness > 10) {
            $issues[] = "Public awareness out of range: {$world->publicAwareness}";
        }
        
        // Check for valid power centers
        if ($world->powerCenters < 0) {
            $issues[] = "Negative power centers: {$world->powerCenters}";
        }
        
        // Check faction integrity
        foreach ($world->factions as $faction) {
            if (empty($faction->id)) {
                $issues[] = "Faction with empty ID found";
            }
            
            if (empty($faction->name)) {
                $issues[] = "Faction with empty name found: {$faction->id}";
            }
            
            if (isset($faction->cohesion) && ($faction->cohesion < 0 || $faction->cohesion > 100)) {
                $issues[] = "Invalid cohesion for faction {$faction->id}: {$faction->cohesion}";
            }
        }
        
        return $issues;
    }
}
