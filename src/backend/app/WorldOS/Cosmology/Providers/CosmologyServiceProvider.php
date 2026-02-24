<?php

declare(strict_types=1);

namespace App\WorldOS\Cosmology\Providers;

use App\WorldOS\Cosmology\Services\WorldEvolutionKernel;
use Illuminate\Support\ServiceProvider;

/**
 * Cosmology Module Service Provider.
 *
 * Registers orchestration services ONLY.
 * Computation engine bindings are in SimulationEngineServiceProvider.
 */
class CosmologyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WorldEvolutionKernel::class);
    }

    public function boot(): void
    {
        // Future: register event listeners for evolution-related events
    }
}
