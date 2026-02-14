<?php

namespace App\StoryEngine\Events;

use App\StoryEngine\WorldState;

abstract class WorldEvent
{
    public int $chapter;
    public string $timelineId;
    public string $type;

    public function __construct(int $chapter, string $timelineId)
    {
        $this->chapter = $chapter;
        $this->timelineId = $timelineId;
        $this->type = static::class;
    }

    abstract public function apply(WorldState $world): void;
    
    public function toArray(): array
    {
        return [
            'chapter' => $this->chapter,
            'timeline_id' => $this->timelineId,
            'type' => $this->type,
            // Subclasses should merge their payload here
        ];
    }
}
