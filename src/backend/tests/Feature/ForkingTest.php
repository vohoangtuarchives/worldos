<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\WorldSeeder;
use App\Models\World;
use Tuzy\Application\World\Services\WorldForkService;
use Tuzy\Application\World\Services\EventRecorder;

class ForkingTest extends TestCase
{
    use RefreshDatabase;

    public function test_forking_copies_history_correctly()
    {
        // 1. Seed World A
        $this->seed(WorldSeeder::class);
        $worldA = World::first();
        
        // Advance A further
        $recorder = app(EventRecorder::class);
        // Current tick is probably around 20-30 after seeding
        // Let's record an event at a known high tick
        
        // Force tick to 100 via loop or service
        // Creating event at Tick 100 (assuming clock allows jumping or we just record it)
        // Clock automatically ticks (+1) in recorder.
        // Let's just use what we have.
        
        $initialTick = $worldA->clock->current_tick;
        
        // Add "Future Event" for World A
        $futureEvent = $recorder->record($worldA, 'future.event', ['note' => 'Only in Timeline A']);
        $futureTick = $futureEvent->tick;

        // 2. Fork at `initialTick` (BEFORE the future event)
        $forkService = app(WorldForkService::class);
        $worldB = $forkService->fork($worldA, $initialTick, 'Timeline B');

        // 3. Asset World B exists
        $this->assertNotEquals($worldA->id, $worldB->id);
        $this->assertEquals('Timeline B', $worldB->name);

        // 4. Assert World B has events <= initialTick
        // Retrieve seeded event (e.g., city.founded) logic
        // Seeder creates 'city.founded' at early tick.
        $this->assertDatabaseHas('world_events', [
            'world_id' => $worldB->id,
            'type' => 'city.founded', // Should exist
        ]);

        // 5. Assert World B does NOT have events > initialTick
        $this->assertDatabaseMissing('world_events', [
            'world_id' => $worldB->id,
            'type' => 'future.event', // Should NOT exist in B
        ]);

        // 6. Assert World A still has future event
        $this->assertDatabaseHas('world_events', [
            'world_id' => $worldA->id,
            'type' => 'future.event',
        ]);
        
        // 7. Check Scars (Seeder created a scar for 'drought.started')
        // Ensure scar was copied if event was copied
        // Drought started at tick ~3-4 in seeder?
        // Let's verify scar exists in B
        $this->assertDatabaseHas('world_scars', [
            'world_id' => $worldB->id,
            // 'weight' => 3 // From seeder
        ]);
    }
}
