<?php

namespace Tuzy\Infrastructure\Material\Providers;

use Illuminate\Support\ServiceProvider;
use Tuzy\Domain\Material\Contracts\MaterialRepositoryInterface;
use Tuzy\Infrastructure\Material\Repositories\MaterialEloquentRepository;

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
