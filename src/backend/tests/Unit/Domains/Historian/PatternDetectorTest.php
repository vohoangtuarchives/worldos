<?php

namespace Tests\Unit\Domains\Historian;

use Tests\TestCase;
use Tuzy\Domain\Historian\PatternDetector;
use Tuzy\Domain\Saga\Saga;
use Tuzy\Domain\Saga\SagaWorld;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class PatternDetectorTest extends TestCase
{
    use RefreshDatabase;

    private PatternDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new PatternDetector();
    }

    public function test_detects_dominance_patterns()
    {
        // Create a saga with repeated archetype dominance
        $saga = Saga::create([
            'id' => Str::uuid(),
            'name' => 'Dominance Saga',
            'status' => Saga::STATUS_COMPLETED
        ]);

        // Create 3 worlds where 'hero' is dominant
        for ($i = 0; $i < 3; $i++) {
            $world = \App\Models\World::create([
                'name' => "World $i",
                'status' => 'active'
            ]);
            
            SagaWorld::create([
                'saga_id' => $saga->id,
                'world_id' => $world->id,
                'sequence' => $i,
                'status' => SagaWorld::STATUS_COMPLETED,
                'archetype_legacy' => [
                    'hero' => ['type' => 'dominance', 'intensity' => 0.9]
                ]
            ]);
            \Tuzy\Domain\Saga\SagaObservation::create([
                'saga_id' => $saga->id,
                'world_id' => $world->id,
                'tick' => 100,
                'observation_type' => 'archetype_shift', // SagaObservation::TYPE_ARCHETYPE_SHIFT
                'observation' => 'Hero dominance',
                'context' => ['archetype' => 'hero']
            ]);
        }

        $patterns = $this->detector->detectAcrossSagas([$saga->id]);

        $this->assertArrayHasKey('archetype_patterns', $patterns);
        $this->assertNotEmpty($patterns['archetype_patterns']['patterns']);
        $this->assertStringContainsString("'hero' appears in most sagas", $patterns['archetype_patterns']['patterns'][0]);
    }

    public function test_detects_collapse_triggers()
    {
        // Create a saga with a collapse caused by 'shadow'
        $saga = Saga::create([
            'id' => Str::uuid(),
            'name' => 'Collapse Saga',
            'status' => Saga::STATUS_COMPLETED
        ]);

        $world = \App\Models\World::create([
            'name' => "Collapse World",
            'status' => 'active'
        ]);

        SagaWorld::create([
            'saga_id' => $saga->id,
            'world_id' => $world->id,
            'sequence' => 0,
            'status' => SagaWorld::STATUS_COLLAPSED,
            'collapse_type' => 'chaos_cascade',
            'metadata' => [
                'collapse_context' => [
                    'dominant_archetype' => 'shadow'
                ]
            ]
        ]);

        \Tuzy\Domain\Saga\SagaObservation::create([
            'saga_id' => $saga->id,
            'world_id' => $world->id,
            'tick' => 50,
            'observation_type' => \Tuzy\Domain\Saga\SagaObservation::TYPE_PATTERN,
            'observation' => 'Collapse triggered by shadow',
            'context' => ['archetype' => 'shadow']
        ]);

        $patterns = $this->detector->detectAcrossSagas([$saga->id]);

        $this->assertArrayHasKey('collapse_patterns', $patterns);
        $this->assertArrayHasKey('shadow', $patterns['collapse_patterns']['collapse_triggers']); 
    }
}
