<?php

declare(strict_types=1);

namespace App\WorldOS\SimulationEngine\Providers;

use App\WorldOS\Cosmology\Contracts\CascadeEngineInterface;
use App\WorldOS\Cosmology\Contracts\FeasibilityCheckerInterface;
use App\WorldOS\Cosmology\Contracts\PhysicsEngineInterface;
use App\WorldOS\Cosmology\Contracts\StabilityAnalyzerInterface;
use App\WorldOS\SimulationEngine\Cascade\CascadeEvolutionEngine;
use App\WorldOS\SimulationEngine\Feasibility\FeasibilityChecker;
use App\WorldOS\SimulationEngine\Physics\BasePhysicsEngine;
use App\WorldOS\SimulationEngine\Stability\StabilityAnalyzer;
use Illuminate\Support\ServiceProvider;

/**
 * SimulationEngine Module Service Provider.
 *
 * Binds computation implementations to Cosmology contracts.
 * This is the ONLY place where SimulationEngine knows about Cosmology interfaces.
 *
 * To swap engines (e.g., GPU-accelerated, Rust FFI), change bindings here.
 */
class SimulationEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            PhysicsEngineInterface::class,
            BasePhysicsEngine::class
        );

        $this->app->singleton(
            CascadeEngineInterface::class,
            CascadeEvolutionEngine::class
        );

        $this->app->singleton(
            StabilityAnalyzerInterface::class,
            StabilityAnalyzer::class
        );

        $this->app->singleton(
            FeasibilityCheckerInterface::class,
            FeasibilityChecker::class
        );
    }

    public function boot(): void
    {
        //
    }
}
