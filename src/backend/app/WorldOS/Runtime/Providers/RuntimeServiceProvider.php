<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\Providers;

use App\WorldOS\Runtime\Contracts\UniverseRepositoryInterface;
use App\WorldOS\Runtime\Contracts\UniverseSnapshotRepositoryInterface;
use App\WorldOS\Runtime\Repositories\UniverseEloquentRepository;
use App\WorldOS\Runtime\Repositories\UniverseSnapshotEloquentRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Runtime Module Service Provider.
 *
 * Registers repository bindings.
 */
class RuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
