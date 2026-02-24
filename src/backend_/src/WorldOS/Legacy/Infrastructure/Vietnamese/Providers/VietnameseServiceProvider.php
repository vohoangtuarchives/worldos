<?php

namespace WorldOS\Legacy\Infrastructure\Vietnamese\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use WorldOS\Evolution\Domain\Legacy\Events\WorldTicked;
use WorldOS\Legacy\Application\Vietnamese\Listeners\CheckHeroSpawningListener;

class VietnameseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register services if needed
        $this->app->singleton(\WorldOS\Legacy\Application\Vietnamese\Services\HeroMaterialBridge::class);
        $this->app->singleton(\WorldOS\Legacy\Application\Vietnamese\Services\VietnameseNameGenerator::class);
        $this->app->singleton(\WorldOS\Legacy\Application\Vietnamese\Factories\HeroFactory::class);
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
