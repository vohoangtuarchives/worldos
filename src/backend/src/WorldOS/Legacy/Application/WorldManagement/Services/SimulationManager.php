<?php

namespace WorldOS\Legacy\Application\WorldManagement\Services;

use App\StoryEngine\Simulator;
use App\Models\World;
use App\StoryEngine\Persistence\ReplayEngine;
use App\StoryEngine\Persistence\EventStore;

class SimulationManager
{
    public function __construct(
        protected EventStore $eventStore
    ) {}

    /**
     * Start a simulation run for a specific world.
     */
    public function runSteps(string $worldId, int $steps): array
    {
        // 1. Load World
        $world = World::findOrFail($worldId);
        $timelineId = (string) $world->id; // Simplified mapping for MVP
        
        // 2. Initialize Simulator with State Replay
        $replayer = new ReplayEngine($this->eventStore);
        // Note: ReplayEngine method is 'replay', not 'rebuildState'
        $worldState = $replayer->replay($timelineId); 
        
        // 3. Setup Simulator
        $sim = new Simulator($timelineId);
        $sim->world = $worldState;

        // 4. Run Steps
        // The Simulator run() method returns an array of events/logs
        return $sim->run($steps);
    }
}
