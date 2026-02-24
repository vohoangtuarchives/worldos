<?php

declare(strict_types=1);

namespace App\WorldOS\Resonance\Providers;

use App\WorldOS\Resonance\Contracts\ResonanceCheckerInterface;
use App\WorldOS\Resonance\Listeners\CheckResonanceOnTickListener;
use App\WorldOS\Resonance\Services\HeroResonanceChecker;
use App\WorldOS\Runtime\Events\UniverseTicked;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Resonance Module Service Provider.
 *
 * Binds resonance checker and registers event listeners.
 */
class ResonanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ResonanceCheckerInterface::class,
            HeroResonanceChecker::class
        );
    }

    public function boot(): void
    {
        Event::listen(
            UniverseTicked::class,
            CheckResonanceOnTickListener::class
        );
    }
}
