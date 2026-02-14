<?php

namespace App\StoryEngine\Persistence;

use App\StoryEngine\WorldState;
use App\StoryEngine\Simulator; // To get initial state logic if needed, or just new WorldState

class ReplayEngine
{
    public function __construct(
        protected EventStore $eventStore
    ) {}

    public function replay(string $timelineId): WorldState
    {
        // 1. Bootstrap blank world
        // We need a way to init factions identically to start.
        // Ideally, "WorldInitialized" event should handle this.
        // For now, we assume standard init.
        
        $world = new WorldState();
        // Initialize Factions (identical to Simulator init)
        $world->factions = [
            new \App\StoryEngine\FactionState('sect_1', 'Azure Cloud Sect', 'Sect'),
            new \App\StoryEngine\FactionState('clan_1', 'Iron Blood Clan', 'Clan'),
            new \App\StoryEngine\FactionState('guild_1', 'Golden Pavilion', 'Guild'),
        ];
        
        // 2. Load Events
        $events = $this->eventStore->load($timelineId);

        // 3. Apply Events
        foreach ($events as $row) {
            $payload = json_decode($row->payload, true);
            $type = $row->type;
            
            if (class_exists($type)) {
                // Reconstruct Event Object
                // This assumes constructor signature. 
                // Better approach: Static Factory method on Event class.
                // For FactionActionEvent:
                if ($type === \App\StoryEngine\Events\FactionActionEvent::class) {
                    $event = new $type(
                        $payload['chapter'],
                        $payload['timeline_id'],
                        $payload['faction_id'],
                        $payload['intent'],
                        $payload['outcome']
                    );
                    $event->apply($world);
                }
            }
        }

        return $world;
    }
}
