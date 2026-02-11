<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
//        $this->app->bind(
//            \App\Domains\Narrative\LLM\Contracts\LLMProvider::class,
//            \App\StoryEngine\Services\FakeStoryLLMService::class
//        );
        $this->app->bind(
            \App\Domains\Narrative\LLM\Contracts\LLMProvider::class,
            function ($app) {
                return new \App\Domains\Narrative\LLM\Services\OpenAIService(
                    config('services.openai.api_key'),
                    config('services.openai.model')
                );
            }
        );

        $this->app->bind(
            \App\Domains\World\Contracts\ClaimExtractorInterface::class,
            \App\Domains\World\Services\RegexClaimExtractor::class
        );

        // Phase 31: History & Institution Domains
        $this->app->bind(
            \App\Domains\History\Repositories\ScarRepositoryInterface::class,
            \App\Domains\History\Repositories\ScarEloquentRepository::class
        );
        $this->app->bind(
            \App\Domains\History\Repositories\MythRepositoryInterface::class,
            \App\Domains\History\Repositories\MythEloquentRepository::class
        );
        $this->app->bind(
            \App\Domains\Institution\Repositories\InstitutionRepositoryInterface::class,
            \App\Domains\Institution\Repositories\InstitutionEloquentRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
