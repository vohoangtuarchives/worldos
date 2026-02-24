<?php

declare(strict_types=1);

namespace WorldOS\Shared\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use WorldOS\Chronicle\Domain\Repository\ChronicleRepositoryInterface;
use WorldOS\Chronicle\Infrastructure\Persistence\EloquentChronicleRepository;
use WorldOS\Simulation\Domain\Universe\Repository\UniverseRepositoryInterface;
use WorldOS\Simulation\Infrastructure\Persistence\EloquentUniverseRepository;
use WorldOS\Kernel\Domain\Policy\CompiledPolicy;

final class WorldOSV5ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. Bind Simulation Repositories
        $this->app->bind(
            UniverseRepositoryInterface::class,
            EloquentUniverseRepository::class
        );

        // 2. Bind Chronicle Repositories
        $this->app->bind(
            ChronicleRepositoryInterface::class,
            EloquentChronicleRepository::class
        );

        // 3. Bind Default Policies
        $this->app->singleton(CompiledPolicy::class, function () {
            return CompiledPolicy::baseline();
        });
    }

    public function boot(): void
    {
        //
    }
}
