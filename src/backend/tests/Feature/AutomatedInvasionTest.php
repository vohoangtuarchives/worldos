<?php

namespace Tests\Feature;

use App\Models\World;
use Tuzy\Domain\Saga\Saga;
use Tuzy\Domain\Saga\SagaWorld;
use Tuzy\Domain\Saga\SagaRunner;
use Tuzy\Domain\Saga\Enums\EpicEventType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class AutomatedInvasionTest extends TestCase
{
    use RefreshDatabase;

    public function test_director_triggers_automatic_invasion_on_high_pressure()
    {
        // 1. Setup Saga
        $saga = Saga::create([
            'name' => 'Automated War Saga',
            'world_count' => 2,
            'status' => Saga::STATUS_PENDING,
            'genre' => 'space_opera'
        ]);

        // 2. Setup World #1 (The Void Aggressor)
        $worldVoid = World::create([
            'name' => 'The Hungering Void',
            'status' => 'active',
            'entropy' => 1.0,
            'preset' => 'void',
            'gene_vector' => [],
            'physics_profile' => [
                'instability_rate' => 0.1,
                'entropy_rate' => 2.0,
                'magic_density' => 0.0,
                'cohesion' => 0.2
            ],
            'config' => ['author_persona' => 'VoidChronicler']
        ]);

        SagaWorld::create([
            'saga_id' => $saga->id,
            'world_id' => $worldVoid->id,
            'sequence' => 0,
            'status' => SagaWorld::STATUS_COMPLETED // Mark as completed
        ]);

        // 3. Setup World #2 (The Target)
        $worldTarget = World::create([
            'name' => 'Verdant Prime',
            'status' => 'active',
            'entropy' => 0.1,
            'preset' => 'standard',
            'gene_vector' => [],
            'physics_profile' => [
                'gravity' => 1.0,
                'entropy_rate' => 1.0,
                'magic_density' => 0.5,
                'cohesion' => 1.0
            ],
            'config' => ['author_persona' => 'VerdantChronicler']
        ]);

        SagaWorld::create([
            'saga_id' => $saga->id,
            'world_id' => $worldTarget->id,
            'sequence' => 1,
            'status' => SagaWorld::STATUS_PENDING
        ]);

        // 4. Manually trigger evaluation via Director
        $director = app(\Tuzy\Application\Saga\Services\SagaDirector::class);
        $director->evaluateSaga($saga);

        // 5. Assertions
        $event = \App\Models\WorldEvent::where('world_id', $worldTarget->id)
            ->where('type', EpicEventType::TERRAFORM_EVENT->value)
            ->first();

        $this->assertNotNull($event, "SagaDirector should have triggered a TERRAFORM_EVENT automatically");
        
        // Check if physics drifted (Instability Rate should decrease)
        $worldTarget->refresh();
        $this->assertLessThan(1.0, (float)$worldTarget->physics_profile->instability_rate, "Instability rate should have decreased due to Void invasion");
    }
}
