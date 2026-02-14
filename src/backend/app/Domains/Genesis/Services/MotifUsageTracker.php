<?php

namespace App\Domains\Genesis\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\StoryBlueprint;

class MotifUsageTracker
{
    private const CACHE_PREFIX = 'motif_usage:';

    /**
     * Record the usage of motifs from a finalized blueprint.
     */
    public function recordUsage(StoryBlueprint $blueprint): void
    {
        $this->increment('theme', $blueprint->theme_id);
        $this->increment('conflict', $blueprint->conflict_id);
        $this->increment('power', $blueprint->power_system_id);
    }

    /**
     * Get the saturation penalty for a specific motif.
     * Returns a float 0.0 (Fresh) to 1.0 (Oversaturated).
     */
    public function getSaturationPenalty(string $type, string $id): float
    {
        $count = Cache::get($this->getKey($type, $id), 0);
        
        // Simple linear saturation for now.
        // If used > 10 times recently, it starts to get stale.
        // Cap at 1.0.
        
        $threshold = 10;
        if ($count <= $threshold) {
            return 0.0;
        }

        return min(1.0, ($count - $threshold) / 20); // Reach 1.0 at 30 uses
    }

    private function increment(string $type, string $id): void
    {
        $key = $this->getKey($type, $id);
        if (Cache::has($key)) {
            Cache::increment($key);
        } else {
            Cache::put($key, 1, now()->addDays(7)); // Track weekly saturation
        }
    }

    private function getKey(string $type, string $id): string
    {
        return self::CACHE_PREFIX . "{$type}:{$id}";
    }
}
