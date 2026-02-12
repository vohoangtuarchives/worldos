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
        // LLM Provider binding
        $this->app->bind(
            \App\Domains\Narrative\LLM\Contracts\LLMProvider::class,
            function ($app) {
                return new \App\Domains\Narrative\LLM\Services\OpenAIService(
                    config('services.openai.api_key'),
                    config('services.openai.model')
                );
            }
        );

        // World system bindings
        $this->app->bind(
            \App\Domains\World\Contracts\ClaimExtractorInterface::class,
            \App\Domains\World\Services\RegexClaimExtractor::class
        );

        // Continuous operation services
        $this->app->singleton(\App\Services\World\ContinuousWorldService::class);
        $this->app->singleton(\App\Domains\Intelligence\Services\WorldIntelligenceService::class);
        $this->app->singleton(\App\Domains\Material\Services\WorldMaterialTracker::class);

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
        // Custom response macros
        Response::macro('success', function ($data = null, string $message = 'Success', int $status = 200) {
            return Response::json([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ], $status);
        });

        Response::macro('error', function (string $message, $errors = null, int $status = 400) {
            return Response::json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ], $status);
        });

        Response::macro('world', function ($world, array $additional = []) {
            return Response::json([
                'success' => true,
                'world' => array_merge([
                    'id' => $world->id(),
                    'name' => $world->name(),
                    'tick' => $world->currentTick(),
                    'entropy' => $world->currentEntropy()->value(),
                    'autonomous' => $world->isAutonomous(),
                    'preset' => $world->preset(),
                    'last_tick_at' => $world->lastTickAt()?->format('Y-m-d H:i:s'),
                ], $additional)
            ]);
        });

        // Custom validation rules
        Validator::extend('world_id', function ($attribute, $value, $parameters, $validator) {
            return \App\Domains\World\Repositories\WorldRepository::exists($value);
        });

        Validator::extend('material_id', function ($attribute, $value, $parameters, $validator) {
            return \App\Domains\Material\Repositories\MaterialRepository::exists($value);
        });

        Validator::extend('character_id', function ($attribute, $value, $parameters, $validator) {
            return \App\Domains\Character\Repositories\CharacterSurvivalRepository::exists($value);
        });

        // Request macros for common patterns
        Request::macro('worldId', function () {
            return $this->route('worldId');
        });

        Request::macro('validateWorld', function () {
            return $this->validate([
                'worldId' => 'required|string|world_id',
            ]);
        });
    }
}
