<?php

namespace Tests\Integration\Domains\Saga;

use Tests\TestCase;
use Tuzy\Domain\Saga\Saga;
use Tuzy\Domain\Saga\SagaRunner;
use Tuzy\Domain\CognitiveKernel\Archetype;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class SagaExecutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed some archetypes for the generator to use
        Archetype::create(['key' => 'hero', 'domain' => 'power', 'polarity' => ['order'], 'baseline_weight' => 0.5, 'volatility' => 0.1, 'version' => '1.0']);
        Archetype::create(['key' => 'shadow', 'domain' => 'power', 'polarity' => ['chaos'], 'baseline_weight' => 0.5, 'volatility' => 0.1, 'version' => '1.0']);
        Archetype::create(['key' => 'sage', 'domain' => 'perception', 'polarity' => ['order'], 'baseline_weight' => 0.5, 'volatility' => 0.1, 'version' => '1.0']);
    }

    public function test_saga_runs_to_completion()
    {
        // 1. Create a Saga
        $saga = Saga::create([
            'id' => Str::uuid(),
            'name' => 'Test Saga',
            'archetype_focus' => ['hero', 'shadow'],
            'carry_weight' => 0.5,
            'world_count' => 3, // Small number for speed
            'status' => Saga::STATUS_PENDING
        ]);

        // 2. Run the Saga
        $runner = app(SagaRunner::class);
        $runner->runSync($saga);

        // 3. Verify Completion
        $this->assertEquals(Saga::STATUS_COMPLETED, $saga->fresh()->status);
        $this->assertNotNull($saga->completed_at);

        // 4. Verify Worlds were created
        $this->assertEquals(3, $saga->sagaWorlds()->count());

        // 5. Verify World Data
        $firstWorld = $saga->sagaWorlds()->orderBy('sequence')->first();
        $this->assertNotNull($firstWorld->world_id);
        
        // Check legacy passing (2nd world should have legacy from 1st)
        $secondWorld = $saga->sagaWorlds()->orderBy('sequence')->skip(1)->first();
        // Legacy might be empty if 1st world collapsed too early, but field should exist
        $this->assertIsArray($secondWorld->archetype_legacy);
    }
}
