<?php

namespace Tests\Feature\Console;

use App\Console\Commands\WorldTicker;
use App\Jobs\EvolveWorldJob;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorldTickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_jobs_for_autonomous_worlds()
    {
        Bus::fake();

        // 1. Create 2 worlds: one autonomous, one not
        World::create([
            'id' => Str::uuid(),
            'name' => 'Auto World',
            'autonomous' => true,
            'preset' => 'narrative_generated',
            'gene_vector' => [],
        ]);

        World::create([
            'id' => Str::uuid(),
            'name' => 'Manual World',
            'autonomous' => false,
             'preset' => 'narrative_generated',
             'gene_vector' => [],
        ]);

        // 2. Run command
        $this->artisan('world:tick')
            ->expectsOutputToContain('Dispatching tick for World: Auto World')
            ->assertExitCode(0);

        // 3. Assert Job Dispatched
        Bus::assertDispatched(EvolveWorldJob::class, function ($job) {
             return $job->world->name === 'Auto World';
        });

        Bus::assertNotDispatched(EvolveWorldJob::class, function ($job) {
            return $job->world->name === 'Manual World';
        });
    }
}
