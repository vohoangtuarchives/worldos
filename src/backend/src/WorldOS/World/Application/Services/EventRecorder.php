<?php

namespace WorldOS\World\Application\Services;

use App\Domains\World\Services\WorldClockService;
use App\Models\World;
use App\Models\WorldEvent;
use Illuminate\Support\Facades\DB;

class EventRecorder
{
    public function __construct(
        protected WorldClockService $clockService
    ) {
    }

    public function record(
        World $world,
        string $type,
        array $payload,
        string $visibility = 'Public'
    ): WorldEvent {
        $tick = $this->clockService->now($world);
        return DB::transaction(function () use ($world, $tick, $type, $payload, $visibility) {
            return WorldEvent::create([
                'world_id' => $world->id,
                'tick' => $tick,
                'type' => $type,
                'payload' => array_merge($payload, [
                    'visibility' => $visibility
                ])
            ]);
        });
    }
}
