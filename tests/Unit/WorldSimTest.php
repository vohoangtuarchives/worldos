<?php

namespace Tests\Unit;

use App\Domains\World\Processor\EntropyTickProcessor;
use App\Domains\World\Processor\MythPropagationProcessor;
use App\Domains\World\Services\BasicWorldMetricCalculator;
use App\Domains\World\Services\WorldTickService;
use App\Models\World\WorldPreset;
use App\Models\World\WorldState;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorldSimTest extends TestCase
{
    // use RefreshDatabase; // Use existing seeded data or handle cleanup manually if needed, but RefreshDatabase is safer for repeated runs.
    // However, since I just seeded, RefreshDatabase might wipe it unless I seed in the test.
    // I'll skip RefreshDatabase and cleanup manually to persist the seeded preset if possible, or just re-seed in test.
    // Actually, RefreshDatabase is best practice. I should seed inside the test or use the trait.
    
    use RefreshDatabase;

    public function test_world_simulation_flow()
    {
        // 1. Seed Preset
        $this->seed(\Database\Seeders\WorldPresetSeeder::class);
        
        $preset = WorldPreset::where('code', 'sandbox_test')->first();
        $this->assertNotNull($preset, 'Preset should exist');

        // 2. Create Initial World State
        $initialSnapshot = [
            'meta' => [
                'tick' => 0,
                'seed' => 12345,
            ],
            'world' => [
                'entropy' => 0.1,
                'myth_density' => 0.05,
            ],
            'characters' => [
                'char_1' => [
                    'name' => 'Hero',
                    'attributes' => ['power_base' => 100],
                ]
            ],
            'myths' => [
                'myth_1' => [
                    'spread_level' => 1,
                    'belief_strength' => 0.1,
                ]
            ]
        ];

        $state = WorldState::create([
            'id' => Str::uuid()->toString(),
            'preset_id' => $preset->id,
            'version' => 1,
            'seed' => 12345,
            'snapshot' => $initialSnapshot,
            'created_at' => now(),
        ]);

        // 3. Setup Engine
        $tickService = $this->app->make(WorldTickService::class);
        
        // Add processors manualy for test (or rely on service provider if configured there)
        // I didn't configure processors in provider, so add them here.
        $tickService->addProcessor(new EntropyTickProcessor());
        $tickService->addProcessor(new MythPropagationProcessor());

        // 4. Advance Tick
        $nextState = $tickService->advance($state);

        $this->assertNotNull($nextState);
        $this->assertEquals(1, $nextState->snapshot['meta']['tick']);
        $this->assertGreaterThan(0.1, $nextState->snapshot['world']['entropy'], 'Entropy should increase');
        
        // 5. Calculate Metrics
        $metricCalculator = new BasicWorldMetricCalculator();
        $metrics = $metricCalculator->calculate($nextState->snapshot);
        
        $this->assertEquals(100, $metrics['total_power']);
        $this->assertEquals('char_1', $metrics['strongest_character_id']);
        
        // 6. Verify Determinism (Run again with same seed/tick should produce same result?? No, DB random is used in tests?)
        // The DeterministicRandom logic uses seed + tick. 
        // If I re-run logic on same state:
        
        $nextState2 = $tickService->advance($state);
        
        // Since deterministic, entropy increase should be identical
        $this->assertEquals(
            $nextState->snapshot['world']['entropy'], 
            $nextState2->snapshot['world']['entropy'],
            'determinism check'
        );
    }
}
