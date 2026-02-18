<?php

namespace App\Domains\Power\Repositories;

use App\Domains\Power\WorldEvent;
use Illuminate\Support\Facades\DB;

class WorldEventLedgerRepository
{
    public function save(string $worldId, WorldEvent $event): void
    {
        DB::transaction(function () use ($worldId, $event) {
            // 1. Record the event
            DB::table('world_event_ledger')->insert([
                'world_id'   => $worldId,
                'event_type' => $event->type,
                'magnitude'  => $event->magnitude,
                'permanence' => $event->permanence,
                'visibility' => $event->visibility,
                'epoch'      => $event->epoch,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Accumulate pressure (Simplified: only permanent/long-term signals increase world pressure)
            DB::table('world_power_stages')
                ->where('world_id', $worldId)
                ->increment('accumulated_pressure', $event->magnitude * $event->permanence);
        });
    }

    public function getHistory(string $worldId, int $limit = 50): array
    {
        return DB::table('world_event_ledger')
            ->where('world_id', $worldId)
            ->orderByDesc('epoch')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getActiveEvents(string $worldId)
    {
        return DB::table('world_event_ledger')
            ->where('world_id', $worldId)
            ->get();
    }
}
