<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\World;
use App\Domains\World\Services\WorldForkService;

class ForkWorldCommand extends Command
{
    protected $signature = 'world:fork {world_id} {tick} {new_name}';
    protected $description = 'Fork a world into a new timeline from a specific tick';

    public function handle(WorldForkService $forkService)
    {
        $worldId = $this->argument('world_id');
        $tick = (int) $this->argument('tick');
        $newName = $this->argument('new_name');

        $sourceWorld = World::find($worldId);

        if (! $sourceWorld) {
            $this->error("World #{$worldId} not found.");
            return 1;
        }

        $this->info("Forking world '{$sourceWorld->name}' at tick {$tick}...");

        $newWorld = $forkService->fork($sourceWorld, $tick, $newName);

        $this->info("World forked successfully!");
        $this->info("New World ID: {$newWorld->id}");
        $this->info("New World Name: {$newWorld->name}");
        
        return 0;
    }
}
