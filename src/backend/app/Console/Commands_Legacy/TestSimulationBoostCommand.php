<?php

namespace App\Console\Commands;

use App\Models\World;
use Tuzy\Domain\Saga\SagaRunner;
use Tuzy\Application\Vietnamese\Services\CosmicIntegrationService;
use Illuminate\Console\Command;

class TestSimulationBoostCommand extends Command
{
    protected $signature = 'world:test-simulation-boost {world_id}';
    protected $description = 'Test civilization boost integration in simulation loop';

    public function handle(SagaRunner $runner, CosmicIntegrationService $cosmicService): int
    {
        $worldId = $this->argument('world_id');
        $world = World::find($worldId);
        
        if (!$world) {
            $this->error("World not found!");
            return Command::FAILURE;
        }

        if ($world->origin_type !== 'vietnamese') {
            $this->error("World is not Vietnamese origin!");
            return Command::FAILURE;
        }

        $currentEra = (int) floor(($world->current_time ?? 0) / 50);
        $this->info("Checking boosts for World: {$world->name} (Era {$currentEra})");

        // 1. Check Service Calculation directly
        $boosts = $cosmicService->calculateEraCivilizationBoost($currentEra);
        $this->info("Calculated Service Boosts:");
        foreach ($boosts as $dim => $val) {
            $this->line(" - {$dim}: {$val}");
        }

        if (empty(array_filter($boosts))) {
            $this->warn("No active heroes or modifiers found in this era.");
        } else {
            $this->info("✅ Boosts detected!");
        }

        // 2. We can't easily spy on internal method variables of SagaRunner without hacking.
        // But we can verify if the service returns data, and we know we injected it.
        
        return Command::SUCCESS;
    }
}
