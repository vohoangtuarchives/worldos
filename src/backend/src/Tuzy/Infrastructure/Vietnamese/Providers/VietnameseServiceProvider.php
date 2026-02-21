<?php

namespace Tuzy\Infrastructure\Vietnamese\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Tuzy\Domain\Evolution\Events\WorldTicked;
use Tuzy\Application\Vietnamese\Listeners\CheckHeroSpawningListener;

class VietnameseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register services if needed
        $this->app->singleton(\Tuzy\Application\Vietnamese\Services\HeroMaterialBridge::class);
        $this->app->singleton(\Tuzy\Application\Vietnamese\Services\VietnameseNameGenerator::class);
        $this->app->singleton(\Tuzy\Application\Vietnamese\Factories\HeroFactory::class);
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
