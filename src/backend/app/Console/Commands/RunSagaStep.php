<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Tuzy\Domain\Saga\Saga;
use Tuzy\Domain\Saga\SagaRunner;

class RunSagaStep extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saga:step {saga_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a single step of the saga simulation (Debug)';

    /**
     * Execute the console command.
     */
    public function handle(SagaRunner $runner)
    {
        $sagaId = $this->argument('saga_id');
        $saga = Saga::find($sagaId);

        if (!$saga) {
            $this->error("Saga {$sagaId} not found.");
            return;
        }

        $this->info("Running Saga: {$saga->name}...");

        // Determine current world or create next
        $currentWorld = $saga->getCurrentWorld();
        
        if (!$currentWorld) {
            $this->info("No active world. Creating next world...");
            $currentWorld = $runner->createNextWorld($saga);
        }

        if (!$currentWorld) {
            $this->error("Failed to create/retrieve world.");
            return;
        }

        $this->info("Simulating World: {$currentWorld->world->name} (Tick: {$currentWorld->world->tick}, Time: {$currentWorld->world->current_time})");

        // Force run sync
        $runner->runSync($saga);

        $this->info("Simulation Step Complete.");
        
        $currentWorld->refresh();
        $this->info("New State -> Tick: {$currentWorld->world->tick}, Time: {$currentWorld->world->current_time}");
    }
}
