<?php

namespace WorldOS\Legacy\Domain\CognitiveKernel;

use App\Models\World;
use Illuminate\Support\Collection;

class ArchetypePool
{
    /**
     * Get all archetypes for a specific kernel version
     */
    public function getAll(string $version = '1.0.0'): Collection
    {
        return Archetype::where('version', $version)->get();
    }

    /**
     * Get archetypes by domain
     */
    public function getByDomain(string $domain): Collection
    {
        return Archetype::forDomain($domain);
    }

    /**
     * Get archetype by key
     */
    public function get(string $key): ?Archetype
    {
        return Archetype::findByKey($key);
    }

    /**
     * Initialize archetype weights for a world
     */
    public function initializeForWorld(World $world, ?array $archetypeFocus = null): void
    {
        $archetypes = $this->getAll();

        foreach ($archetypes as $archetype) {
            $weight = $archetype->baseline_weight;

            // If archetype is in focus, increase initial weight
            if ($archetypeFocus && in_array($archetype->key, $archetypeFocus)) {
                $weight = min(1.0, $weight + 0.2);
            }

            ArchetypeWeight::create([
                'world_id' => $world->id,
                'archetype_key' => $archetype->key,
                'weight' => $weight,
                'effective_weight' => $weight,
                'drift_history' => []
            ]);
        }
    }

    /**
     * Get archetype weights for a world
     */
    public function getWeightsForWorld(World $world): Collection
    {
        return ArchetypeWeight::where('world_id', $world->id)->get();
    }

    /**
     * Get active archetypes for a world (weight > threshold)
     */
    public function getActiveForWorld(World $world, float $threshold = 0.5): Collection
    {
        return ArchetypeWeight::where('world_id', $world->id)
            ->where('weight', '>', $threshold)
            ->get();
    }

    /**
     * Get archetype weight for a world
     */
    public function getWeightForWorld(World $world, string $archetypeKey): ?ArchetypeWeight
    {
        return ArchetypeWeight::where('world_id', $world->id)
            ->where('archetype_key', $archetypeKey)
            ->first();
    }

    /**
     * Update archetype weight for a world
     */
    public function updateWeight(World $world, string $archetypeKey, float $newWeight): void
    {
        $weight = $this->getWeightForWorld($world, $archetypeKey);
        
        if ($weight) {
            $weight->weight = max(0, min(1, $newWeight));
            $weight->save();
        }
    }

    /**
     * Get archetypes that support a specific polarity
     */
    public function getByPolarity(string $polarity): Collection
    {
        return Archetype::all()->filter(function ($archetype) use ($polarity) {
            return $archetype->supportsPolarity($polarity);
        });
    }
}
