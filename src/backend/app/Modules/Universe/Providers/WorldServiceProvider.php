<?php

declare(strict_types=1);

namespace App\Modules\Universe\Providers;

use App\Modules\Universe\Contracts\WorldRepositoryInterface;
use App\Modules\Universe\Repositories\WorldEloquentRepository;
use App\Modules\Universe\Contracts\UniverseRepositoryInterface;
use App\Modules\Universe\Repositories\UniverseEloquentRepository;
use App\Modules\Universe\Contracts\UniverseSnapshotRepositoryInterface;
use App\Modules\Universe\Repositories\UniverseSnapshotEloquentRepository;
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

        $this->app->bind(
            UniverseRepositoryInterface::class,
            UniverseEloquentRepository::class
        );

        $this->app->bind(
            UniverseSnapshotRepositoryInterface::class,
            UniverseSnapshotEloquentRepository::class
        );
    }

    public function boot(): void
    {
        // Event listeners will be registered here in future phases
    }
}
