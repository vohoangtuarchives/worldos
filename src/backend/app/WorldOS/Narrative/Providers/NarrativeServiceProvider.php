<?php

declare(strict_types=1);

namespace App\WorldOS\Narrative\Providers;

use App\WorldOS\Narrative\Contracts\LLMChroniclerInterface;
use App\WorldOS\Narrative\Contracts\NarrativeSeriesRepositoryInterface;
use App\WorldOS\Narrative\Repositories\NarrativeSeriesEloquentRepository;
use App\WorldOS\Narrative\Services\NullLLMChronicler;
use Illuminate\Support\ServiceProvider;

class NarrativeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            NarrativeSeriesRepositoryInterface::class,
            NarrativeSeriesEloquentRepository::class
        );

        $this->app->bind(
            LLMChroniclerInterface::class,
            NullLLMChronicler::class
        );
    }

    public function boot(): void
    {
        //
    }
}
