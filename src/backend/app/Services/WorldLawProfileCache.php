<?php

namespace App\Services;

use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldLawProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WorldLawProfileCache
{
    private const CACHE_TTL = 3600; // 1 hour
    private const DEFAULT_PROFILE_KEY = 'world_law_profile_default';
    private const PROFILE_KEY_PREFIX = 'world_law_profile_';

    /**
     * Get world law profile for a world, with caching.
     */
    public function getProfile(string $worldId): ?WorldLawProfile
    {
        $cacheKey = self::PROFILE_KEY_PREFIX . $worldId;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            return $this->loadProfileFromDatabase($worldId);
        });
    }

    /**
     * Get default world law profile.
     */
    public function getDefaultProfile(): WorldLawProfile
    {
        return Cache::remember(self::DEFAULT_PROFILE_KEY, self::CACHE_TTL, function () {
            return WorldLawProfile::default();
        });
    }

    /**
     * Cache a world law profile.
     */
    public function cacheProfile(string $worldId, WorldLawProfile $profile): void
    {
        $cacheKey = self::PROFILE_KEY_PREFIX . $worldId;
        Cache::put($cacheKey, $profile, self::CACHE_TTL);
    }

    /**
     * Invalidate cached profile for a world.
     */
    public function invalidateProfile(string $worldId): void
    {
        $cacheKey = self::PROFILE_KEY_PREFIX . $worldId;
        Cache::forget($cacheKey);
    }

    /**
     * Invalidate all cached profiles.
     */
    public function invalidateAllProfiles(): void
    {
        // In a real implementation with cache tags, this would be easier
        // For now, we'll invalidate the default and log a warning
        Cache::forget(self::DEFAULT_PROFILE_KEY);
        Log::warning('All world law profiles cache invalidated - consider implementing cache tags');
    }

    /**
     * Warm up cache for commonly used profiles.
     */
    public function warmUpCache(array $worldIds = []): void
    {
        // Always warm up default profile
        $this->getDefaultProfile();

        // Warm up specific world profiles if provided
        foreach ($worldIds as $worldId) {
            $this->getProfile($worldId);
        }

        // Warm up recently active worlds
        $this->warmUpRecentlyActiveWorlds();
    }

    /**
     * Get cache statistics.
     */
    public function getCacheStats(): array
    {
        return [
            'default_profile_cached' => Cache::has(self::DEFAULT_PROFILE_KEY),
            'cached_profiles_count' => $this->getCachedProfilesCount(),
            'cache_ttl' => self::CACHE_TTL,
        ];
    }

    /**
     * Load profile from database.
     */
    private function loadProfileFromDatabase(string $worldId): ?WorldLawProfile
    {
        try {
            $world = \App\Models\World::find($worldId);
            
            if (!$world) {
                Log::warning("World {$worldId} not found for law profile loading");
                return null;
            }

            // Extract law profile from world attributes or create default
            if (isset($world->law_profile)) {
                return WorldLawProfile::fromArray($world->law_profile);
            }

            // Return default profile if no specific profile exists
            return $this->getDefaultProfile();
            
        } catch (\Exception $e) {
            Log::error("Failed to load world law profile for world {$worldId}: " . $e->getMessage());
            return $this->getDefaultProfile();
        }
    }

    /**
     * Warm up cache for recently active worlds.
     */
    private function warmUpRecentlyActiveWorlds(): void
    {
        try {
            $recentWorlds = \App\Models\World::orderBy('updated_at', 'desc')
                ->limit(10)
                ->pluck('id');

            foreach ($recentWorlds as $worldId) {
                $this->getProfile($worldId);
            }

            Log::info('Warmed up cache for ' . $recentWorlds->count() . ' recently active worlds');
            
        } catch (\Exception $e) {
            Log::error("Failed to warm up recently active worlds: " . $e->getMessage());
        }
    }

    /**
     * Get count of cached profiles (approximate).
     */
    private function getCachedProfilesCount(): int
    {
        // This is a rough estimate since we can't easily count keys by pattern
        // without cache tags or a more sophisticated caching system
        try {
            $count = 0;
            $worldIds = \App\Models\World::limit(100)->pluck('id');
            
            foreach ($worldIds as $worldId) {
                $cacheKey = self::PROFILE_KEY_PREFIX . $worldId;
                if (Cache::has($cacheKey)) {
                    $count++;
                }
            }
            
            return $count;
        } catch (\Exception $e) {
            Log::error("Failed to count cached profiles: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Preload profiles for a batch of worlds.
     */
    public function preloadProfiles(array $worldIds): array
    {
        $profiles = [];
        $uncachedIds = [];

        // Check cache first
        foreach ($worldIds as $worldId) {
            $cacheKey = self::PROFILE_KEY_PREFIX . $worldId;
            if (Cache::has($cacheKey)) {
                $profiles[$worldId] = Cache::get($cacheKey);
            } else {
                $uncachedIds[] = $worldId;
            }
        }

        // Load uncached profiles from database in batch
        if (!empty($uncachedIds)) {
            $worlds = \App\Models\World::whereIn('id', $uncachedIds)->get();
            
            foreach ($worlds as $world) {
                $profile = $this->loadProfileFromDatabase($world->id);
                if ($profile) {
                    $profiles[$world->id] = $profile;
                    $this->cacheProfile($world->id, $profile);
                }
            }
        }

        return $profiles;
    }
}
