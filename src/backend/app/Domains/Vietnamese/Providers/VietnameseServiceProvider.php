<?php

namespace App\Domains\Vietnamese\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Domains\Evolution\Events\WorldTicked;
use App\Domains\Vietnamese\Listeners\CheckHeroSpawningListener;

class VietnameseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register services if needed
        $this->app->singleton(\App\Domains\Vietnamese\Services\HeroMaterialBridge::class);
        $this->app->singleton(\App\Domains\Vietnamese\Services\VietnameseNameGenerator::class);
        $this->app->singleton(\App\Domains\Vietnamese\Factories\HeroFactory::class);
    }

    public function boot(): void
    {
        // Register Event Listeners
        Event::listen(
            WorldTicked::class,
            CheckHeroSpawningListener::class
        );
    }
}
