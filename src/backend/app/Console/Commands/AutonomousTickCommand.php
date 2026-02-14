<?php

namespace App\Console\Commands;

use App\Jobs\TickMetaJob;
use App\Jobs\TickWorldJob;
use App\Models\World;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AutonomousTickCommand extends Command
{
    protected $signature = 'autonomous:tick {--force}';
    protected $description = 'Trigger a single tick for the Autonomous Engine';

    public function handle(): void
    {
        // 1. Get Current Global Tick
        // We can store this in Cache or DB. Let's use Cache for speed or MetaLayerState
        // For now, let's assume we increment a cache counter.
        $currentTick = Cache::increment('autonomous_engine_tick');
        
        $this->info("Starting Tick {$currentTick}...");

        // 2. Dispatch Meta Tick
        TickMetaJob::dispatch($currentTick);
        $this->info("Dispatched Meta Tick");

        // 3. Dispatch World Ticks
        $worlds = World::where('autonomous', true)
            ->get();

        foreach ($worlds as $world) {
            TickWorldJob::dispatch($world->id, $currentTick);
        }

        $this->info("Dispatched {$worlds->count()} World Ticks");
    }
}
