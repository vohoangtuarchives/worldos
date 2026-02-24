<?php

declare(strict_types=1);

namespace App\WorldOS\Style\Providers;

use App\WorldOS\Style\Contracts\StyleRepositoryInterface;
use App\WorldOS\Style\Repositories\StyleEloquentRepository;
use Illuminate\Support\ServiceProvider;

class StyleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            StyleRepositoryInterface::class,
            StyleEloquentRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
