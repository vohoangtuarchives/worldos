<?php

namespace App\Console\Commands;

use App\Jobs\EvolveWorldJob;
use App\Models\World;
use Illuminate\Console\Command;

class WorldTicker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world:tick {--id= : The ID of the world to tick (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process a time tick for one or all autonomous worlds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $worldId = $this->option('id');

        if ($worldId) {
            $this->tickSingleWorld($worldId);
        } else {
            $this->tickAutonomousWorlds();
        }
    }

    protected function tickSingleWorld(string $id)
    {
        $world = World::find($id);

        if (!$world) {
            $this->error("World with ID {$id} not found.");
            return;
        }

        $this->info("Dispatching tick for World: {$world->name} ({$id})");
        EvolveWorldJob::dispatch($world);
    }

    protected function tickAutonomousWorlds()
    {
        $this->info("Finding autonomous worlds...");

        World::where('autonomous', true)
            ->chunk(100, function ($worlds) {
                foreach ($worlds as $world) {
                    $this->info("Dispatching tick for World: {$world->name}");
                    EvolveWorldJob::dispatch($world);
                }
            });
            
        $this->info("Tick dispatch complete.");
    }
}
