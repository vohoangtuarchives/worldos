<?php

namespace App\StoryEngine\Events;

use App\StoryEngine\MemoryRecorder;
use App\StoryEngine\WorldState;

class FactionActionEvent extends WorldEvent
{
    public string $factionId;
    public string $intent;
    public string $outcome; // success | failure | disaster

    public function __construct(
        int $chapter,
        string $timelineId,
        string $factionId,
        string $intent,
        string $outcome
    ) {
        parent::__construct($chapter, $timelineId);
        $this->factionId = $factionId;
        $this->intent = $intent;
        $this->outcome = $outcome;
    }

    public function apply(WorldState $world): void
    {
        // Find the faction in the world state
        $faction = null;
        foreach ($world->factions as $f) {
            if ($f->id === $this->factionId) {
                $faction = $f;
                break;
            }
        }

        if ($faction) {
            // Apply outcome to memory
            MemoryRecorder::recordOutcome($faction, $this->intent, $this->outcome);
        }
    }
    
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'faction_id' => $this->factionId,
            'intent' => $this->intent,
            'outcome' => $this->outcome,
        ]);
    }
}
