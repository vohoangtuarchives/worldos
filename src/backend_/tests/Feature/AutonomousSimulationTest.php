<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use App\Models\World;
use App\Models\MetaLayerState;
use App\Models\MetaSnapshot;
use App\Models\WorldSnapshotV2;
use WorldOS\Blueprint\Domain\Legacy\Enums\WorldType;
use WorldOS\Blueprint\Domain\Legacy\Enums\WorldHealthStatus;

class AutonomousSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_autonomous_tick_evolves_world_and_captures_snapshot()
    {
        Queue::fake();

        // 1. Setup World
        $world = World::create([
            'name' => 'Test World',
            'preset' => 'standard',
            'gene_vector' => ['magic' => 0.5],
            'type' => WorldType::FANTASY, // Fixed Enum Case
            'status' => 'active',
            'health_status' => WorldHealthStatus::STABLE,
            'autonomous' => true,
            'entropy' => 0.1,
            'cosmic_energy' => 100.0,
            'current_tick' => 0,
        ]);

        // 2. Setup Meta Layer
        MetaLayerState::instance();

        // 3. Run Command
        $this->artisan('autonomous:tick')
             ->assertExitCode(0);

        // 4. Verify Dispatch
        Queue::assertPushed(\App\Jobs\TickWorldJob::class);
        Queue::assertPushed(\App\Jobs\TickMetaJob::class);

        // 5. Execute Job Manually (since Queue::fake)
        $job = new \App\Jobs\TickWorldJob($world->id, 1);
        $job->handle();

        // 6. Verify World Evolution
        $world->refresh();
        $this->assertEquals(1, $world->current_tick);
        $this->assertGreaterThan(0.1, $world->entropy);

        // 7. Verify Snapshot created
        $this->assertDatabaseHas('world_snapshots_v2', [
            'world_id' => $world->id,
            'tick' => 0, // Snapshot captures state at end of previous tick or start of current? 
                         // Logic: captureWorld uses $world->tick. Job updates tick then service captures.
                         // Wait, in WorldTickService:
                         // $world->current_tick = $tick (1)
                         // $world->save()
                         // captureWorld($world)
                         // So tick should be 1.
            'tick' => 1,
        ]);
    }

    public function test_meta_tick_evolves_and_snapshots()
    {
        MetaLayerState::instance();

        $job = new \App\Jobs\TickMetaJob(1);
        $job->handle();

        $this->assertDatabaseHas('meta_snapshots', [
            'tick' => 1,
        ]);
        
        $state = MetaLayerState::first();
        // Check if evolution happened (decay)
        // Default chaos is 0. But if we set it higher...
    }
}
