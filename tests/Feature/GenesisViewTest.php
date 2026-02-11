<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class GenesisViewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the genesis page loads and contains the mixing panel.
     *
     * @return void
     */
    public function test_genesis_page_loads_with_mixing_panel()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->get(route('writer.genesis'));

        $response->assertStatus(200);
        
        // Check for key UI elements
        $response->assertSee('Khai Thiên Tịch Địa');
        $response->assertSee('Mixing Panel');
        
        // Check for Select inputs
        $response->assertSee('name="power_system"', false);
        $response->assertSee('name="tech_level"', false);
        $response->assertSee('name="environment"', false);
        
        // Check that Enums are populated (random check)
        // Check that Enums are populated (random check)
        $response->assertSee('Linh Khí'); 
        $response->assertSee('SPIRITUAL_QI');
    }
}
