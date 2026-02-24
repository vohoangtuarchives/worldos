<?php

declare(strict_types=1);

namespace App\WorldOS\Attractor\Providers;

use App\WorldOS\Attractor\Contracts\AttractorRepositoryInterface;
use App\WorldOS\Attractor\Repositories\AttractorEloquentRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Attractor Module Service Provider.
 */
class AttractorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AttractorRepositoryInterface::class,
            AttractorEloquentRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
