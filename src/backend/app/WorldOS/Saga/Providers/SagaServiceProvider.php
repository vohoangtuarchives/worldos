<?php

declare(strict_types=1);

namespace App\WorldOS\Saga\Providers;

use App\WorldOS\Saga\Contracts\SagaRepositoryInterface;
use App\WorldOS\Saga\Repositories\SagaEloquentRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Saga Module Service Provider.
 */
class SagaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            SagaRepositoryInterface::class,
            SagaEloquentRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
