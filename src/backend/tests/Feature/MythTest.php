<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\WorldSeeder;
use App\Models\World;
use App\Models\WorldBelief;
use App\Models\WorldMyth;
use App\Domains\World\Services\BeliefRecorder;
use App\Domains\World\Services\MythEmergenceService;

class MythTest extends TestCase
{
    use RefreshDatabase;

    public function test_belief_recording_and_myth_emergence()
    {
        // 1. Seed World
        $this->seed(WorldSeeder::class);
        $world = World::first();

        // 2. Record belief multiple times
        $recorder = app(BeliefRecorder::class);
        $content = 'Hạn hán là hình phạt cho những kẻ bỏ quên tổ tiên';

        // Check initial state
        $this->assertDatabaseMissing('world_myths', ['name' => $content]);

        // Record 3 times (Threshold is 3)
        $recorder->record($world, $content);
        $recorder->record($world, $content);
        $recorder->record($world, $content);

        // Verify belief stats
        $this->assertDatabaseHas('world_beliefs', [
            'world_id' => $world->id,
            'content' => $content,
            'repeat_count' => 3,
            'intensity' => 3
        ]);

        // 3. Run Myth Emergence
        $service = app(MythEmergenceService::class);
        $service->check($world);

        // 4. Verify Myth Creation
        $this->assertDatabaseHas('world_myths', [
            'world_id' => $world->id,
            'name' => $content,
            'status' => 'active',
            'strength' => 3
        ]);

        // 5. Verify Narrative Change
        // The narrative should now reflect the active myth
        $response = $this->get('/story');
        $response->assertSee('lời sấm truyền về sự trừng phạt');
    }
}
