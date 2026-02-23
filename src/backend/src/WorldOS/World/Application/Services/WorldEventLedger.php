<?php

namespace WorldOS\World\Application\Services;

use App\Models\World;
use App\Models\WorldEvent;
use Illuminate\Support\Collection;

class WorldEventLedger
{
    public function record(
        World $world,
        string $type,
        string $description,
        float $magnitude = 0.1,
        float $permanence = 1.0,
        string $visibility = 'Public',
        array $metadata = []
    ): WorldEvent {
        $event = WorldEvent::create([
            'world_id' => $world->id,
            'tick' => $world->tick,
            'type' => $type,
            'payload' => [
                'description' => $description,
                'magnitude' => max(0.0, min(1.0, $magnitude)),
                'permanence' => max(0.0, min(1.0, $permanence)),
                'visibility' => $visibility,
                'metadata' => $metadata
            ]
        ]);

        return $event;
    }

    public function calculateGlobalPressure(World $world): float
    {
        $events = WorldEvent::where('world_id', $world->id)->get();
        
        $totalPressure = 0.0;
        foreach ($events as $event) {
            $payload = $event->payload;
            $visibility = $payload['visibility'] ?? 'Public';
            
            if ($visibility === 'Public') {
                $mag = $payload['magnitude'] ?? 0.1;
                $perm = $payload['permanence'] ?? 1.0;
                $totalPressure += ($mag * $perm);
            }
        }

        return $totalPressure;
    }

    public function getSignificantHistory(World $world, float $minMagnitude = 0.5): Collection
    {
        return WorldEvent::where('world_id', $world->id)
            ->get()
            ->filter(function ($event) use ($minMagnitude) {
                return ($event->payload['magnitude'] ?? 0) >= $minMagnitude;
            });
    }

    public function getRecentSocialEvents(World $world, int $limit = 5): array
    {
        $events = WorldEvent::where('world_id', $world->id)
            ->orderByDesc('tick')
            ->limit($limit * 3)
            ->get();

        return $events
            ->filter(function ($event) {
                $visibility = $event->payload['visibility'] ?? 'Public';
                return $visibility === 'Public';
            })
            ->take($limit)
            ->map(function ($event) {
                return [
                    'tick' => $event->tick,
                    'type' => $event->type,
                    'description' => $event->payload['description'] ?? '',
                    'magnitude' => $event->payload['magnitude'] ?? null,
                ];
            })
            ->values()
            ->all();
    }
}
