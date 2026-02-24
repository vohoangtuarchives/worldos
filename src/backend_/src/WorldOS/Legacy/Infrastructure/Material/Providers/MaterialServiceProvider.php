<?php

namespace WorldOS\Legacy\Infrastructure\Material\Providers;

use Illuminate\Support\ServiceProvider;
use WorldOS\Legacy\Domain\Material\Contracts\MaterialRepositoryInterface;
use WorldOS\Legacy\Infrastructure\Material\Repositories\MaterialEloquentRepository;

class MaterialServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            MaterialRepositoryInterface::class,
            MaterialEloquentRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
