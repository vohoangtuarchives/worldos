<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Infrastructure\Cosmic\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Cosmic\Contracts\CosmicSnapshotRepositoryInterface;
use App\Domains\Cosmic\Repositories\CosmicSnapshotEloquentRepository;

class CosmicServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CosmicSnapshotRepositoryInterface::class,
            CosmicSnapshotEloquentRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
