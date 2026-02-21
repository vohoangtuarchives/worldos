<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id(),
            'name' => $this->name(),
            'preset' => $this->preset(),
            'tick' => $this->currentTick(),
            'entropy' => $this->currentEntropy()->value(),
            'autonomous' => $this->isAutonomous(),
            'last_tick_at' => $this->lastTickAt()?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt()->format('Y-m-d H:i:s'),
            
            // Extended information
            'entropy_level' => $this->getEntropyLevel(),
            'lifecycle_phase' => $this->getLifecyclePhase(),
            'is_stable' => $this->isStable(),
            'needs_attention' => $this->needsAttention(),
            
            // Relationships
            'characters_count' => $this->when(
                $this->characters_count !== null,
                $this->characters_count
            ),
            'materials_count' => $this->when(
                $this->materials_count !== null,
                $this->materials_count
            ),
            
            // Metadata
            'links' => [
                'self' => route('worlds.show', $this->id()),
                'dashboard' => route('worlds.dashboard', $this->id()),
                'api' => [
                    'status' => route('worlds.status', $this->id()),
                    'intelligence' => route('worlds.intelligence', $this->id()),
                    'materials' => route('worlds.materials', $this->id()),
                    'realtime' => route('worlds.realtime', $this->id()),
                ]
            ]
        ];
    }
}

class WorldCollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'data' => WorldResource::collection($this->collection),
            'meta' => [
                'total' => $this->collection->count(),
                'autonomous_count' => $this->collection->filter(fn($w) => $w->isAutonomous())->count(),
                'running_count' => $this->collection->filter(fn($w) => $w->isRunning())->count(),
                'average_entropy' => $this->collection->avg(fn($w) => $w->currentEntropy()->value()),
                'average_tick' => $this->collection->avg(fn($w) => $w->currentTick()),
            ],
            'links' => [
                'self' => route('worlds.index'),
                'create' => route('worlds.create'),
            ]
        ];
    }
}
