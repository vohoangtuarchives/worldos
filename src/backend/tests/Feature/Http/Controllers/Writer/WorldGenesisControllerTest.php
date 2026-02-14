<?php

namespace Tests\Feature\Http\Controllers\Writer;

use App\Domains\Narrative\Models\MaterialSeed;
use App\Domains\Narrative\Models\StoryPremise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorldGenesisControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_it_incarnates_a_world_from_premise()
    {
        // 1. Arrange: Create a premise
        $seed = MaterialSeed::create([
            'id' => Str::uuid(),
            'type' => 'power_system',
            'name' => 'Test System',
            'compatibility_tags' => [],
            'attributes' => []
        ]);

        $premise = StoryPremise::create([
            'id' => Str::uuid(),
            'title' => 'Genesis Saga',
            'summary' => 'A test summary.',
            'components' => ['power_system' => $seed->id],
            'power_escalation' => [],
        ]);

        // 2. Act: Post to the endpoint
        $response = $this->actingAs($this->user)
            ->post(route('writer.genesis.incarnate', $premise->id));

        // 3. Assert: Redirect and World Created
        $response->assertRedirect(route('writer.materials.library'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('worlds', [
            'name' => 'Genesis Saga',
            'preset' => 'narrative_generated'
        ]);
        
        // Assert World State exists (via Evolution Kernel)
        $this->assertDatabaseHas('world_states', [
            'structural_anchor' => 'Genesis'
        ]);
    }
}
