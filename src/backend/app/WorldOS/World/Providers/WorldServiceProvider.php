<?php

declare(strict_types=1);

namespace App\WorldOS\World\Providers;

use App\WorldOS\World\Contracts\WorldRepositoryInterface;
use App\WorldOS\World\Repositories\WorldEloquentRepository;
use Illuminate\Support\ServiceProvider;

/**
 * World Module Service Provider.
 *
 * Registers repository bindings and event listeners.
 */
class WorldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            WorldRepositoryInterface::class,
            WorldEloquentRepository::class
        );
    }

    public function boot(): void
    {
        // Event listeners will be registered here in future phases
    }
}
