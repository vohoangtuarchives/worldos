<?php

namespace Tests\Feature\Tuzy;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateWorldEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_with_name_returns_201_and_json_with_id_and_name(): void
    {
        $response = $this->postJson('/api/v4/tuzy/worlds', ['name' => 'Feature Test World']);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'name']);
        $response->assertJsonFragment(['name' => 'Feature Test World']);
        $this->assertNotEmpty($response->json('id'));
    }

    public function test_post_without_name_returns_422(): void
    {
        $response = $this->postJson('/api/v4/tuzy/worlds', []);

        $response->assertStatus(422);
    }
}
