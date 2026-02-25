<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Providers;

use App\Modules\Narrative\Contracts\LLMChroniclerInterface;
use App\Modules\Narrative\Contracts\NarrativeSeriesRepositoryInterface;
use App\Modules\Narrative\Contracts\SagaRepositoryInterface;
use App\Modules\Narrative\Repositories\NarrativeSeriesEloquentRepository;
use App\Modules\Narrative\Repositories\SagaEloquentRepository;
use App\Modules\Narrative\Services\NullLLMChronicler;
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
            SagaRepositoryInterface::class,
            SagaEloquentRepository::class
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
