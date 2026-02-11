<?php

namespace App\Domains\Material\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Material\Contracts\MaterialRepositoryInterface;
use App\Domains\Material\Repositories\MaterialEloquentRepository;

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
