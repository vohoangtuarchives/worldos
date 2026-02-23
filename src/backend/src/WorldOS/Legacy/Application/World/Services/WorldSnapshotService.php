<?php

namespace WorldOS\Legacy\Application\World\Services;

use App\Models\World;
use App\Models\WorldSnapshot;

class WorldSnapshotService
{
    /**
     * Create a named bookmark at the current world tick.
     */
    public function createSnapshot(World $world, string $name, ?string $description = null): WorldSnapshot
    {
        // Get current tick (could optimize by passing clock if already loaded)
        $currentTick = $world->clock->current_tick;

        return WorldSnapshot::create([
            'world_id'    => $world->id,
            'tick'        => $currentTick,
            'name'        => $name,
            'description' => $description,
        ]);
    }
}
