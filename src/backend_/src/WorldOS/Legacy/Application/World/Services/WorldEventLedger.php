<?php

namespace WorldOS\Legacy\Application\World\Services;

use App\Models\World;
use App\Models\WorldEvent;
use Illuminate\Support\Collection;

class WorldEventLedger
{
    /**
     * Record an event in the World Ledger
     */
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

    /**
     * Calculate Total Global Pressure
     * Pressure = Σ(Magnitude * Permanence) for all Public events
     */
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

    /**
     * Get Significant History (Higher Magnitude)
     */
    public function getSignificantHistory(World $world, float $minMagnitude = 0.5): Collection
    {
        return WorldEvent::where('world_id', $world->id)
            ->get()
            ->filter(function ($event) use ($minMagnitude) {
                return ($event->payload['magnitude'] ?? 0) >= $minMagnitude;
            });
    }
}
