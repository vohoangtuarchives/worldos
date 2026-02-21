<?php

namespace Tests\Feature\Domains\Narrative;

use Tuzy\Domain\Narrative\Models\MaterialSeed;
use Tuzy\Domain\Narrative\Models\StoryPremise;
use Tuzy\Application\Narrative\Services\MaterialGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class MaterialGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected MaterialGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new MaterialGenerator();
        
        // Seed the database with known data for testing
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

    public function test_it_generates_a_premise()
    {
        $premise = $this->generator->generatePremise();

        $this->assertInstanceOf(StoryPremise::class, $premise);
        $this->assertDatabaseHas('narrative_story_premises', ['id' => $premise->id]);
        
        $this->assertArrayHasKey('power_system', $premise->components);
        $this->assertArrayHasKey('social_structure', $premise->components);
        $this->assertNotNull($premise->title);
        $this->assertNotNull($premise->summary);
        
        // Check escalation logic (custom implementation in seed or defaults)
        // Since we created 'Test Cultivation', it might fall back to default or match string.
        // The generator checks for 'cultivation' in name.
        $this->assertIsArray($premise->power_escalation);
    }
}
