<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all worlds
        $worlds = \App\Models\World::all();

        foreach ($worlds as $world) {
            // Check if factions exist
            if ($world->factions()->count() === 0) {
                // Seed default 3 factions
                \App\Models\Faction::create([
                    'world_id' => $world->id,
                    'name' => 'Azure Cloud Sect',
                    'type' => 'Sect',
                    'attributes' => ['cohesion' => 80],
                ]);
                
                \App\Models\Faction::create([
                    'world_id' => $world->id,
                    'name' => 'Iron Blood Clan',
                    'type' => 'Clan',
                    'attributes' => ['cohesion' => 85],
                ]);

                \App\Models\Faction::create([
                    'world_id' => $world->id,
                    'name' => 'Golden Pavilion',
                    'type' => 'Guild',
                    'attributes' => ['cohesion' => 90],
                ]);
            }
        }
    }
}
