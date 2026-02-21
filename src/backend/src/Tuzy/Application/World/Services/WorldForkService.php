<?php

namespace Tuzy\Application\World\Services;

use App\Models\World;
use App\Models\WorldClock;
use App\Models\WorldEvent;
use App\Models\WorldScar;
use Illuminate\Support\Facades\DB;

class WorldForkService
{
    /**
     * Fork a world at a specific tick to create a new timeline.
     * This copies historical events/scars up to that point.
     * @param World $sourceWorld
     * @param int $atTick
     * @param string $newName
     * @param \Tuzy\Domain\World\ValueObject\WorldLawProfile|null $newProfile (ADR-0006)
     */
    public function fork(
        World $sourceWorld,
        int $atTick,
        string $newName,
        ?\Tuzy\Domain\World\ValueObject\WorldLawProfile $newProfile = null
    ): World {
        return DB::transaction(function () use ($sourceWorld, $atTick, $newName, $newProfile) {
            // 1. Create New World
            // ADR-0006: Child world inherits parent laws unless explicitly overridden
            $lawProfile = $newProfile ?? $sourceWorld->law_profile;
            
            $newWorld = World::create([
                'name' => $newName,
                'law_profile' => $lawProfile,
                'parent_id' => $sourceWorld->id, // Track Lineage
                'preset' => $sourceWorld->preset ?? 'vietnamese_mythology', // Default fallback
                'gene_vector' => $sourceWorld->gene_vector,
                'origin_type' => $sourceWorld->origin_type,
                'chaos_seed' => $sourceWorld->chaos_seed, // Share seed or new? Usually fork shares base seed but diverges
                // 'type' removed as column does not exist
            ]);

            // 2. Initialize Clock at the fork point
            WorldClock::create([
                'world_id'     => $newWorld->id,
                'current_tick' => $atTick,
            ]);

            // 3. Copy Events (Bulk Insert & ID Mapping)
            // We need to map Old ID -> New ID to correctly attach Scars
            $oldToNewEventMap = [];

            $sourceEvents = WorldEvent::query()
                ->where('world_id', $sourceWorld->id)
                ->where('tick', '<=', $atTick)
                ->orderBy('tick') // Important for order
                ->get();

            foreach ($sourceEvents as $oldEvent) {
                // Manually create to bypass "immutability" checks if any
                // But actually, create() is fine as long as we don't update later.
                // However, we MUST use raw insert or disable timestamps if we want to preserve historical accuracy?
                // For simplicity, we just create new records.
                
                // Note: We use replication to make a copy
                $newEvent = $oldEvent->replicate(['world_id', 'created_at', 'updated_at']);
                $newEvent->world_id = $newWorld->id;
                $newEvent->save();

                $oldToNewEventMap[$oldEvent->id] = $newEvent->id;
            }

            // 4. Copy Scars
            // Only copy scars that originated from events we just copied
            $sourceScars = WorldScar::query()
                ->where('world_id', $sourceWorld->id)
                ->whereIn('source_event_id', array_keys($oldToNewEventMap))
                ->get();

            foreach ($sourceScars as $oldScar) {
                $newScar = $oldScar->replicate(['world_id', 'source_event_id', 'created_at', 'updated_at']);
                $newScar->world_id = $newWorld->id;
                // Remap to the NEW event ID in the NEW world
                $newScar->source_event_id = $oldToNewEventMap[$oldScar->source_event_id];
                $newScar->save();
            }

            return $newWorld;
        });
    }
}
