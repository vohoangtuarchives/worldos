<?php

namespace WorldOS\Legacy\Application\World\Services;

use App\Models\WorldEvent;
use App\Models\WorldScar;

class ScarFactory
{
    /**
     * Create a permanent scar from an event.
     * Scars are the long-term consequences of events.
     */
    public function createFromEvent(
        WorldEvent $event,
        int $weight = 1
    ): WorldScar {
        return WorldScar::create([
            'world_id'        => $event->world_id,
            'source_event_id' => $event->id,
            'weight'          => $weight,
        ]);
    }
}
