<?php

namespace App\StoryEngine\Persistence;

use App\StoryEngine\Events\WorldEvent;
use Illuminate\Support\Facades\DB;

class EventStore
{
    public function append(WorldEvent $event): void
    {
        DB::table('world_events')->insert([
            'timeline_id' => $event->timelineId,
            'chapter' => $event->chapter,
            'tick' => $event->chapter, // Legacy support
            'type' => get_class($event),
            'payload' => json_encode($event->toArray()), // Serialize full object
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function load(string $timelineId): iterable
    {
        return DB::table('world_events')
            ->where('timeline_id', $timelineId)
            ->orderBy('chapter')
            ->get();
    }
}
