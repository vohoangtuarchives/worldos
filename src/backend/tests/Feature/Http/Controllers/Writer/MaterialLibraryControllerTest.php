<?php

namespace Tests\Feature\Http\Controllers\Writer;

use Tuzy\Domain\Narrative\Models\MaterialSeed;
use Tuzy\Domain\Narrative\Models\StoryPremise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class MaterialLibraryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user to authenticate as
        $this->user = User::factory()->create();
        
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

        MaterialSeed::create([
            'id' => Str::uuid(),
            'type' => 'social_structure',
            'name' => 'Test Sects',
            'compatibility_tags' => ['ancient'],
        ]);

        MaterialSeed::create([
            'id' => Str::uuid(),
            'type' => 'twist',
            'name' => 'Test Rebirth',
            'compatibility_tags' => ['any'],
        ]);

         MaterialSeed::create([
            'id' => Str::uuid(),
            'type' => 'hidden_truth',
            'name' => 'Test Matrix',
            'compatibility_tags' => ['sci-fi'],
        ]);
    }

    public function test_index_page_loads()
    {
        $response = $this->actingAs($this->user)
                         ->get(route('writer.materials.library'));

        $response->assertStatus(200);
        $response->assertViewIs('writer.materials.index');
        $response->assertViewHas('seeds');
        $response->assertSee('Narrative Material Engine');
    }

    public function test_generate_action()
    {
        $response = $this->actingAs($this->user)
                         ->post(route('writer.materials.generate'), [
                             'power_system' => '', // Random
                             'social_structure' => '',
                             'twist' => '',
                             'hidden_truth' => '',
                         ]);

        $response->assertRedirect(route('writer.materials.library'));
        $response->assertSessionHas('generated_premise');
        
        $premise = session('generated_premise');
        $this->assertInstanceOf(StoryPremise::class, $premise);
    }

    public function test_save_action()
    {
        // First create a premise
        $premise = StoryPremise::create([
            'id' => Str::uuid(),
            'title' => 'Test Title',
            'summary' => 'Test Summary',
            'components' => [],
            'power_escalation' => [],
            'is_favorite' => false,
        ]);

        $response = $this->actingAs($this->user)
                         ->post(route('writer.materials.save', $premise->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('narrative_story_premises', [
            'id' => $premise->id,
            'is_favorite' => true,
        ]);
    }
}
