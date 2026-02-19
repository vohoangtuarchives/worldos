<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Domains\World\Events\WorldDefined;
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
