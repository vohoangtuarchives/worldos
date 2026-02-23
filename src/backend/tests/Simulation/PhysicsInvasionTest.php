<?php

namespace Tests\Simulation;

use Tests\TestCase;
use App\Models\World;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\PhysicsProfile;
use WorldOS\Legacy\Application\Saga\Services\PhysicsMutator;
use WorldOS\Legacy\Application\Saga\Actions\TerraformWorldAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PhysicsInvasionTest extends TestCase
{
    use RefreshDatabase;

    public function test_void_invasion_mutates_standard_world()
    {
        \Illuminate\Database\Eloquent\Model::unguard();
        
        // 1. Setup World A (Standard Physics)
        $standardProfile = new PhysicsProfile(
            instability_rate: 0.1,
            decay_rate: 0.01,
            entropy_cap: 1000.0,
            mutation_chance: 0.01,
            dimensional_permeability: 0.2, // Low permeability (High resistance)
            energy_conservation_factor: 0.9
        );

        $standardProfileJson = json_encode($standardProfile->toArray());
        
        // Define void profile early
        $voidProfile = new PhysicsProfile(
            instability_rate: 0.9,
            decay_rate: 0.5,
            entropy_cap: 5000.0,
            mutation_chance: 0.5,
            dimensional_permeability: 1.0, 
            energy_conservation_factor: 0.5
        );
        $voidProfileJson = json_encode($voidProfile->toArray());
        
        $targetId = \Illuminate\Support\Facades\DB::table('worlds')->insertGetId([
            'name' => 'Target World ' . microtime(true) . rand(100,999),
            'physics_profile' => $standardProfileJson,
            'entropy' => 100.0,
            'tick' => 0,
            'status' => 'active',
            'preset' => 'standard',
            'gene_vector' => '[]',
            'genre' => 'fantasy',
            'config' => '[]',
            'current_time' => 0.0,
            'calendar_system' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $worldTarget = World::find($targetId);

        $voidId = \Illuminate\Support\Facades\DB::table('worlds')->insertGetId([
            'name' => 'Void World ' . microtime(true) . rand(100,999),
            'physics_profile' => $voidProfileJson,
            'entropy' => 4000.0,
            'tick' => 0,
            'status' => 'active',
            'preset' => 'void',
            'gene_vector' => '[]',
            'genre' => 'scifi',
            'config' => '[]',
            'current_time' => 0.0,
            'calendar_system' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $worldVoid = World::find($voidId);

        // 3. Inject Action
        $action = app(TerraformWorldAction::class);

        // 4. Run Invasion (Terraform Attempt)
        // This injects entropy and forces drift
        $result = $action->handle($worldVoid, $worldTarget);

        // 5. Verify Results
        
        // Refresh models
        $worldTarget->refresh();
        $worldVoid->refresh();

        // Check Entropy Transfer
        $this->assertGreaterThan(100.0, $worldTarget->entropy, "Target entropy should increase");
        
        // CHECK PHYSICS MUTATION
        // Original Instability: 0.1
        // Void Instability: 0.9
        // Expected: Should move towards 0.9
        $newProfile = $worldTarget->physics_profile;
        
        $this->assertGreaterThan(0.1, $newProfile->instability_rate, "Instability should increase towards Void");
        $this->assertLessThan(0.9, $newProfile->instability_rate, "Instability should not fully become Void instantly");
        
        echo "\n--- Invasion Results ---\n";
        echo "Target Entropy: 100 -> {$worldTarget->entropy}\n";
        echo "Target Instability: 0.1 -> {$newProfile->instability_rate}\n";
        echo "Entropy Transferred: {$result['entropy_transferred']}\n";

        // 6. Verify Narrative
        $event = \App\Models\WorldEvent::where('world_id', $worldTarget->id)
            ->where('type', 'terraform_event')
            ->first();

        $this->assertNotNull($event, "A TERRAFORM_EVENT should be recorded");
        
        $narrator = app(\WorldOS\Legacy\Application\Saga\Services\LedgerNarrator::class);
        $narrative = $narrator->narrate($event);
        
        $this->assertNotNull($narrative, "Narrative should be generated");
        echo "Generated Narrative: {$narrative}\n";
    }
}
