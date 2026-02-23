<?php

namespace Tests\Feature\Domains\Evolution;

use WorldOS\Legacy\Application\Evolution\Services\GenesisService;
use WorldOS\Saga\Domain\Narrative\Models\MaterialSeed;
use WorldOS\Saga\Domain\Narrative\Models\StoryPremise;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GenesisServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GenesisService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GenesisService();
        
        // Seed necessary data
        $this->seedMaterials();
    }

    protected function seedMaterials()
    {
        MaterialSeed::create([
            'id' => Str::uuid(),
            'type' => 'power_system',
            'name' => 'Test Cultivation',
            'compatibility_tags' => ['ancient'],
            'attributes' => ['scaling' => 'infinite']
        ]);
    }

    public function test_it_incarnates_a_premise_into_a_world()
    {
        // 1. Create a Premise
        $seed = MaterialSeed::first();
        $premise = StoryPremise::create([
            'id' => Str::uuid(),
            'title' => 'Incarnated Saga',
            'summary' => 'A world born from code.',
            'components' => ['power_system' => $seed->id],
            'power_escalation' => [],
        ]);

        // 2. Run Incarnation
        $world = $this->service->incarnate($premise);

        // 3. Assert World Created
        $this->assertInstanceOf(World::class, $world);
        $this->assertDatabaseHas('worlds', [
            'id' => $world->id,
            'name' => 'Incarnated Saga',
        ]);

        // 4. Assert World State Created with Evolution Data
        $this->assertDatabaseHas('world_states', [
            'structural_anchor' => 'Genesis',
            // Check linking via relationship logic or direct query if needed
        ]);

        // Verify the state vector JSON
        $state = \DB::table('world_states')->where('structural_anchor', 'Genesis')->first();
        $this->assertNotNull($state->state_vector);
        $vector = json_decode($state->state_vector, true);
        
        // Cultivation logic adds 0.4 to baseline 0.1 belief_mass = 0.5
        $this->assertEquals(0.5, $vector['belief_mass']);
        
        // Verify Evolution Profile linked
        $this->assertNotNull($state->evolution_profile_id);
    }
}
