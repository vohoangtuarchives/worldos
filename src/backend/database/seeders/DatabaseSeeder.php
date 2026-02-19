<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        $this->call([
            AttractorSeeder::class,           // ← NEW: Seed cosmic attractors FIRST
            MaterialSeeder::class,
            AdvancedMaterialSeeder::class, // ← NEW: Seed advanced mutation sources/targets
            TechnologyInteractionMaterialsSeeder::class,
            ArchetypeSeeder::class,
            WorldSeeder::class,
            \Database\Seeders\WorldPresetSeeder::class,
            
            // Vietnamese Cultural System
            ScoringVersionSeeder::class,      // ← Seed scoring config first
            RealmContactSeeder::class,        // ← Seed realm contacts
            VietnameseFullHeroSeeder::class,  // ← Seed FULL hero database (96+ heroes)
        ]);
    }
}
