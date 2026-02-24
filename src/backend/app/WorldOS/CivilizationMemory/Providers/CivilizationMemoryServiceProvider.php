<?php

declare(strict_types=1);

namespace App\WorldOS\CivilizationMemory\Providers;

use App\WorldOS\CivilizationMemory\Contracts\MythRepositoryInterface;
use App\WorldOS\CivilizationMemory\Contracts\ScarRepositoryInterface;
use App\WorldOS\CivilizationMemory\Repositories\MythEloquentRepository;
use App\WorldOS\CivilizationMemory\Repositories\ScarEloquentRepository;
use Illuminate\Support\ServiceProvider;

/**
 * CivilizationMemory Module Service Provider.
 */
class CivilizationMemoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ScarRepositoryInterface::class,
            ScarEloquentRepository::class
        );

        $this->app->bind(
            MythRepositoryInterface::class,
            MythEloquentRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
