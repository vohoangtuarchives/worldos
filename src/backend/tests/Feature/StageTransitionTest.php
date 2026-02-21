<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Domains\Power\StageTransitionEngine;
use App\Domains\Power\Services\WorldPressureService;
use Tuzy\Domain\Power\ValueObject\PowerStage;
use Illuminate\Support\Facades\DB;

class StageTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_world_moves_through_transition_phases()
    {
        // 1. Setup World
        $worldId = DB::table('worlds')->insertGetId([
            'name' => 'Test World',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('world_power_stages')->insert([
            'world_id' => $worldId,
            'current_stage' => PowerStage::STAGE_0_MUNDANE->value,
            'accumulated_pressure' => 0.0,
            'transition_phase' => 'stable',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pressureService = app(WorldPressureService::class);
        $engine = app(StageTransitionEngine::class);

        // 2. Increase Pressure (e.g. 0.5 >= 0.4 for Mortal Martial)
        // We simulate this by directly updating the ledger or just calling a mock
        // For simplicity in this test, let's manually insert a heavy event
        DB::table('world_event_ledger')->insert([
            'world_id' => $worldId,
            'event_type' => 'seal_crack',
            'magnitude' => 0.6,
            'permanence' => 1.0,
            'visibility' => 'rumor',
            'epoch' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Evaluate -> Should move to PRE
        $pressureService->checkTransition($worldId);
        
        $state = DB::table('world_power_stages')->where('world_id', $worldId)->first();
        $this->assertEquals('pre', $state->transition_phase);
        $this->assertEquals(PowerStage::STAGE_1_MORTAL_MARTIAL->value, $state->target_stage);

        // 4. Trigger Moment
        $engine->triggerMoment($worldId);
        $state = DB::table('world_power_stages')->where('world_id', $worldId)->first();
        $this->assertEquals('moment', $state->transition_phase);

        // 5. Start Stabilization
        $engine->startStabilization($worldId);
        $state = DB::table('world_power_stages')->where('world_id', $worldId)->first();
        $this->assertEquals('post', $state->transition_phase);

        // 6. Complete Transition
        $engine->completeTransition($worldId);
        $state = DB::table('world_power_stages')->where('world_id', $worldId)->first();
        $this->assertEquals('stable', $state->transition_phase);
        $this->assertEquals(PowerStage::STAGE_1_MORTAL_MARTIAL->value, $state->current_stage);
        $this->assertNull($state->target_stage);
    }
}
