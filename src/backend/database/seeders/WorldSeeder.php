<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\World;
use App\Models\WorldClock;
use App\Models\Observer;
use App\Models\ObserverVersion;
use App\Domains\World\Services\EventRecorder;
use App\Domains\World\Services\ScarFactory;

class WorldSeeder extends Seeder
{
    public function run(): void
    {
        $world = World::firstOrCreate(
            ['name' => 'The First Chronicle'],
            [
                'description' => 'A world for the Material Law Engine simulation',
                'current_epoch' => 0,
                'status' => 'active',
                'preset' => 'myth',
                'gene_vector' => [],
            ]
        );

        $this->command->info("World created: {$world->name}");

        // 1. Initialize WorldState (Phase 13)
        $repository = app(\App\Domains\Material\State\WorldStateRepository::class);
        $initialState = \App\Domains\Material\State\WorldState::createInitial($world->id);
        
        // Save initial snapshot
        $repository->saveSnapshot($initialState);
        $this->command->info("WorldState initialized for World {$world->id}");

        // 2. Activate Materials (Phase 14)
        $materials = \App\Domains\Material\Material::all();
        
        if ($materials->isEmpty()) {
            $this->command->warn('No materials found. Running MaterialSeeder...');
            $this->call(MaterialSeeder::class);
            $materials = \App\Domains\Material\Material::all();
        }

        $activatedCount = 0;
        foreach ($materials as $material) {
            // Activate ~50% of materials with random strength
            if (rand(0, 100) > 50) {
                \App\Domains\Material\MaterialInstance::firstOrCreate(
                    [
                        'world_id' => $world->id,
                        'material_id' => $material->id,
                    ],
                    [
                        'strength_level' => rand(3, 8), // 3-8 strength
                        'activation_epoch' => 0,
                        'retired_at' => null,
                        'mutation_state' => ['original' => true],
                    ]
                );
                $activatedCount++;
            }
        }

        $this->command->info("Activated {$activatedCount} materials in World {$world->id}");

        // 3. Keep Observers (Legacy Support)
        WorldClock::firstOrCreate(['world_id' => $world->id]);
        
        $chronicler = Observer::firstOrCreate(['name' => 'chronicler', 'role' => 'chronicler']);
        ObserverVersion::firstOrCreate(['observer_id' => $chronicler->id, 'version' => 'v1'], ['rules' => ['tone' => 'neutral']]);
    }
}
