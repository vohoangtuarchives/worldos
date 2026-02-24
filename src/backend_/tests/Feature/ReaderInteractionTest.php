<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\WorldSeeder;
use App\Models\World;
use Illuminate\Support\Str;

class ReaderInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_reader_can_react_to_world()
    {
        // 1. Seed World
        $this->seed(WorldSeeder::class);
        $world = World::first();

        $sessionId = (string) Str::uuid();

        // 2. Post Reaction
        $response = $this->postJson("/api/world/{$world->id}/react", [
            'tick' => 50,
            'type' => 'candle',
            'session_id' => $sessionId
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reader_reactions', [
            'world_id' => $world->id,
            'tick' => 50,
            'type' => 'candle',
        ]);

        // 3. Verify Session Created
        $this->assertDatabaseHas('reader_sessions', [
            'id' => $sessionId,
            'world_id' => $world->id
        ]);
    }

    public function test_can_retrieve_aggregated_reactions()
    {
        $this->seed(WorldSeeder::class);
        $world = World::first();
        $sessionId = (string) Str::uuid();

        // Add 2 candles at tick 100
        $this->postJson("/api/world/{$world->id}/react", ['tick' => 100, 'type' => 'candle', 'session_id' => $sessionId]);
        $this->postJson("/api/world/{$world->id}/react", ['tick' => 100, 'type' => 'candle', 'session_id' => $sessionId]);
        
        // Add 1 flower at tick 100
        $this->postJson("/api/world/{$world->id}/react", ['tick' => 100, 'type' => 'flower', 'session_id' => $sessionId]);

        // Retrieve
        $response = $this->getJson("/api/world/{$world->id}/reactions");

        $response->assertStatus(200);
        
        // Check structure
        $data = $response->json();
        
        // Should have 2 entries for tick 100 (candle: 2, flower: 1)
        // Note: Response order isn't guaranteed, so we filter or just check JSON fragment
        $response->assertJsonFragment(['tick' => 100, 'type' => 'candle', 'count' => 2]);
        $response->assertJsonFragment(['tick' => 100, 'type' => 'flower', 'count' => 1]);
    }
}
