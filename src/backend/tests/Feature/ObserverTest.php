<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\WorldSeeder;
use App\Models\World;
use App\Domains\World\Services\EventRecorder;

class ObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_chronicler_view_is_neutral()
    {
        $this->seed(WorldSeeder::class);

        $response = $this->get('/story?observer=chronicler');

        $response->assertStatus(200);
        // Neutral tone check (no prefix)
        $response->assertDontSee('Người đời sau kể lại');
        $response->assertDontSee('Có ghi chép vụn vặt');
        // Check content presence
        $response->assertSee('Ashenfall');
        $response->assertSee('nghi lễ'); // Chronicler sees rituals
    }

    public function test_skeptic_view_is_biased_and_filtered()
    {
        $this->seed(WorldSeeder::class);

        // Skeptic ignores 'ritual.performed'
        $response = $this->get('/story?observer=skeptic');

        $response->assertStatus(200);
        
        // Tone check
        $response->assertSee('Có ghi chép vụn vặt'); // "Skeptic tone"
        
        // Filter check
        $response->assertDontSee('nghi lễ'); // Rituals are ignored
        
        // But city foundation is still seen
        $response->assertSee('Ashenfall');
    }

    public function test_believer_view_is_mythic()
    {
        $this->seed(WorldSeeder::class);

        $response = $this->get('/story?observer=believer');

        $response->assertStatus(200);

        // Tone check
        $response->assertSee('Truyền thuyết ca ngợi');
        
        // Believer definitely sees rituals
        $response->assertSee('nghi lễ');
    }
}
