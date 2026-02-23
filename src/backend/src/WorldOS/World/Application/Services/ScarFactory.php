<?php

namespace WorldOS\World\Application\Services;

use App\Models\WorldEvent;
use App\Models\WorldScar;

class ScarFactory
{
    public function createFromEvent(
        WorldEvent $event,
        int $weight = 1
    ): WorldScar {
        return WorldScar::create([
            'world_id' => $event->world_id,
            'source_event_id' => $event->id,
            'weight' => $weight,
        ]);
    }
}
