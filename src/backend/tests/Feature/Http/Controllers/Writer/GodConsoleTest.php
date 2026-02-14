<?php

namespace Tests\Feature\Http\Controllers\Writer;

use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GodConsoleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected World $world;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        
        // Create a world with a state
        $this->world = World::create([
            'id' => Str::uuid(),
            'name' => 'God Test World',
            'slug' => 'god-test-world',
            'is_active' => true,
            'preset' => 'narrative_generated',
            'gene_vector' => [],
            'current_tick' => 0,
            'autonomous' => false,
            'entropy' => 0.5,
            'lifecycle_phase' => 'stable',
        ]);

        $this->world->state()->create([
            'state_vector' => [
                'coherence' => 0.5,
                'entropy' => 0.5,
                'belief_mass' => 0.5,
                'resource_flow' => 0.5,
                'stability' => 0.5,
                'innovation_rate' => 0.5,
            ],
            'current_phase' => 'stable',
            'structural_anchor' => 'Genesis',
            'power_axis' => [],
            'resource_axis' => [],
            'perception_axis' => [],
            'author_intent' => [],
        ]);
    }

    public function test_dashboard_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('writer.god_console.index', $this->world->id));

        $response->assertStatus(200);
        $response->assertSee('God Console: God Test World');
        $response->assertSee('Current Phase: <span class="font-mono text-amber-400">Stable</span>', false);
    }

    public function test_metrics_api_returns_json()
    {
        $response = $this->actingAs($this->user)
            ->get(route('writer.god_console.metrics', $this->world->id));

        $response->assertStatus(200);
        $response->assertJsonStructure(['tick', 'state_vector', 'phase']);
        $response->assertJsonFragment(['phase' => 'stable']);
    }

    public function test_intervention_modifies_state()
    {
        // Initial Belief
        $initialBelief = $this->world->state->state_vector['belief_mass'];

        $response = $this->actingAs($this->user)
            ->post(route('writer.god_console.intervene', $this->world->id), [
                'action' => 'inject_belief'
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Refresh world state
        $this->world->refresh();
        $newBelief = $this->world->state->state_vector['belief_mass'];

        // Assert belief increased (0.5 + 0.1 = 0.6)
        $this->assertEquals(0.6, $newBelief);
    }

    public function test_smite_increases_entropy()
    {
         // Initial Entropy
         $initialEntropy = $this->world->state->state_vector['entropy'];

         $response = $this->actingAs($this->user)
             ->post(route('writer.god_console.intervene', $this->world->id), [
                 'action' => 'smite'
             ]);
 
         $response->assertRedirect();
         
         $this->world->refresh();
         $newEntropy = $this->world->state->state_vector['entropy'];
 
         // Assert entropy increased (0.5 + 0.2 = 0.7)
         $this->assertEquals(0.7, $newEntropy);
    }
}
