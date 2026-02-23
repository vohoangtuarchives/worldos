<?php

namespace App\Console\Commands;

use WorldOS\Legacy\Application\Vietnamese\Services\VietnameseOriginService;
use Illuminate\Console\Command;

class CreateVietnameseWorldCommand extends Command
{
    protected $signature = 'world:create-vietnamese {name?} {--entropy=0.95} {--energy=0.80} {--seed=}';
    
    protected $description = 'Create a Vietnamese origin world with Trăm Trứng mythology';
    
    public function handle(VietnameseOriginService $service): int
    {
        $name = $this->argument('name') ?? 'Việt Nam - Trăm Trứng';
        $entropy = (float) $this->option('entropy');
        $energy = (float) $this->option('energy');
        $seed = $this->option('seed') ? (int) $this->option('seed') : random_int(1, 999999);
        
        $this->info("Creating Vietnamese origin world...");
        $this->info("Name: {$name}");
        $this->info("Entropy: {$entropy}, Energy: {$energy}, Seed: {$seed}");
        
        $world = $service->createVietnameseWorld([
            'name' => $name,
            'chaos_seed' => $seed,
            'initial_entropy' => $entropy,
            'initial_energy' => $energy,
        ]);
        
        $this->newLine();
        $this->info("✅ World created successfully!");
        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $world->id],
                ['Name', $world->name],
                ['Origin Type', $world->origin_type],
                ['Chaos Seed', $world->origin_metadata['mythology'] ?? 'N/A'],
                ['Initial Entropy', $world->initial_entropy],
                ['Initial Energy', $world->initial_energy],
                ['Mountain/Sea Split Era', $world->origin_metadata['mountain_sea_split_era'] ?? 'N/A'],
                ['Activated Heroes', count($world->origin_metadata['activated_heroes'] ?? [])],
            ]
        );
        
        return Command::SUCCESS;
    }
}
