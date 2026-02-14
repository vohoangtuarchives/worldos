<?php

namespace App\Domains\World\Services;

use App\Models\World;
use App\Models\WorldEvent;
use Illuminate\Support\Facades\DB;

class EventRecorder
{
    public function __construct(
        protected WorldClockService $clockService
    ) {}

    /**
     * Record a new event in the World.
     * This is the ONLY entry point for creating events.
     * The World Clock is automatically advanced.
     */
    public function record(
        World $world,
        string $type,
        array $payload = []
    ): WorldEvent {
        return DB::transaction(function () use ($world, $type, $payload) {
            // 1. Tick the world clock first
            $tick = $this->clockService->tick($world);

            // 2. Record the event at this tick
            return WorldEvent::create([
                'world_id' => $world->id,
                'tick'     => $tick,
                'type'     => $type,
                'payload'  => $payload,
            ]);
        });
    }
}
