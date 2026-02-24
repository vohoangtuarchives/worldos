<?php

namespace Tests\Feature\Jobs;

use App\Jobs\EvolveWorldJob;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EvolveWorldJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_evolves_world_state()
    {
        // 1. Setup World
        $world = World::create([
            'id' => Str::uuid(),
            'name' => 'Evolving World',
            'slug' => 'evolving-world',
            'is_active' => true,
            'preset' => 'narrative_generated',
            'gene_vector' => [],
            'current_tick' => 0,
            'autonomous' => true,
            'entropy' => 0.5,
            'lifecycle_phase' => 'stable',
        ]);

        $world->state()->create([
            'state_vector' => [
                'coherence' => 0.5,
                'entropy' => 0.5,
                'belief_mass' => 0.5,
                'resource_flow' => 0.5,
                'stability' => 0.5,
                'innovation_rate' => 0.5,
            ],
            'current_phase' => 'stable',
            'structural_anchor' => 'Genesis',
            'power_axis' => [],
            'resource_axis' => [],
            'perception_axis' => [],
            'author_intent' => [],
        ]);

        // Evolution Profile (Default created by Kernel if missing? No, job checks for it)
        // Wait, TickWorldJob checks for profile?
        // Let's check logic: if (!$state || !$state->evolutionProfile) Log::warning
        // So we MUST attach a profile.
        
        $profile = \WorldOS\Evolution\Domain\Legacy\Models\EvolutionProfile::create([
             'id' => Str::uuid(),
             'name' => 'Test Profile',
             'coefficients' => ['entropy_decay' => 0.05],
             'thresholds' => [],
             'alpha' => 1.0,
             'is_active' => true,
        ]);
        
        $world->state->evolutionProfile()->associate($profile);
        $world->state->save();

        // 2. Run Job Synchronously
        $job = new EvolveWorldJob($world);
        $kernel = app(\WorldOS\Legacy\Application\Evolution\Services\EvolutionKernel::class);
        $job->handle($kernel);

        // 3. Assertions
        $world->refresh();
        $this->assertEquals(1, $world->current_tick);
        
        $newState = $world->state->state_vector;
        // Entropy should change based on kernel logic
        $this->assertNotEquals(0.5, $newState['entropy']);
    }
}
