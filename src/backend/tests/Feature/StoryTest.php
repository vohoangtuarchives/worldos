<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\WorldSeeder;

class StoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_endpoint_returns_content()
    {
        // 1. Seed the world
        $this->seed(WorldSeeder::class);

        // 2. Hit the endpoint
        $response = $this->get('/story');

        // 3. Assert
        $response->assertStatus(200);
        $response->assertSee('The Chronicle');
        $response->assertSee('Ashenfall'); // From seeded event
        $response->assertSee('Tiếp tục');
    }

    public function test_story_endpoint_pagination()
    {
        $this->seed(WorldSeeder::class);

        // Get first slice
        $response = $this->get('/story');
        $response->assertStatus(200);

        // Extract next cursor (simple regex or just check existence)
        $response->assertSee('cursor=');
    }

    public function test_story_endpoint_empty_world()
    {
        // No seed
        $response = $this->get('/story');

        $response->assertStatus(200);
        $response->assertSee('Chưa có thế giới nào được tạo');
    }
}
