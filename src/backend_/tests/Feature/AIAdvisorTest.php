<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\WorldSeeder;
use App\Models\World;
use App\Models\WorldMyth;
use App\Models\WorldScar;
use WorldOS\Legacy\Application\World\AI\ArchitectAdvisor;

class AIAdvisorTest extends TestCase
{
    use RefreshDatabase;

    public function test_myth_overgrowth_detection()
    {
        // 1. Seed World
        $this->seed(WorldSeeder::class);
        $world = World::first();

        // 2. Create a dominant myth manually
        WorldMyth::create([
            'world_id' => $world->id,
            'name' => 'The Eternal Sun',
            'strength' => 100, // Very high strength
            'status' => 'active',
        ]);

        // 3. Run Advisor
        $advisor = app(ArchitectAdvisor::class);
        $advisor->analyze($world);

        // 4. Verify Report
        $this->assertDatabaseHas('ai_world_reports', [
            'world_id' => $world->id,
            'type' => 'myth_overgrowth',
        ]);
    }

    public function test_scar_cluster_detection()
    {
        $this->seed(WorldSeeder::class);
        $world = World::first();

        // Create 5 scars manually to trigger threshold
        // (Seeder creates 1, so we add 4 more)
        $event = $world->events()->first();
        for ($i = 0; $i < 5; $i++) {
            WorldScar::create([
                'world_id' => $world->id,
                'source_event_id' => $event->id,
                'weight' => 5
            ]);
        }

        $advisor = app(ArchitectAdvisor::class);
        $advisor->analyze($world);

        $this->assertDatabaseHas('ai_world_reports', [
            'world_id' => $world->id,
            'type' => 'scar_cluster',
        ]);
    }
}
