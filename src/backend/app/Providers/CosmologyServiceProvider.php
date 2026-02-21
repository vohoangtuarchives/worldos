<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Tuzy\Domain\World\Event\WorldDefined;
use App\Listeners\Cosmology\InitializeUniverseStyle;

class CosmologyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::listen(WorldDefined::class, InitializeUniverseStyle::class);
    }
}
