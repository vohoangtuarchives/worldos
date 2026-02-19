<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // WorldOS v3: Universe evaluator (Phase 3) — driver via WORLDOS_EVALUATOR_DRIVER
        $this->app->bind(
            \App\Domains\Runtime\Evaluation\UniverseEvaluatorInterface::class,
            function ($app) {
                $driver = config('worldos.evaluator_driver', 'stub');
                if ($driver === 'llm') {
                    return new \App\Domains\Runtime\Evaluation\LLMUniverseEvaluator(
                        $app->make(\App\Domains\Narrative\LLM\Contracts\LLMProvider::class),
                        new \App\Domains\Runtime\Evaluation\StubUniverseEvaluator()
                    );
                }
                return new \App\Domains\Runtime\Evaluation\StubUniverseEvaluator();
            }
        );

        // LLM Provider binding
        $this->app->bind(
            \App\Domains\Narrative\LLM\Contracts\LLMProvider::class,
            function ($app) {
                $apiKey = config('services.openai.api_key');
                if (empty($apiKey) || app()->environment('testing')) {
                    return new \App\Domains\Narrative\LLM\Services\FakeLLMService();
                }
                return new \App\Domains\Narrative\LLM\Services\OpenAIService(
                    $apiKey,
                    config('services.openai.model')
                );
            }
        );

        // World system bindings
        $this->app->bind(
            \App\Domains\World\Contracts\ClaimExtractorInterface::class,
            \App\Domains\World\Services\RegexClaimExtractor::class
        );
        
        // World repository binding
        $this->app->bind(
            \App\Domains\World\Repositories\WorldRepository::class,
            \App\Domains\World\Repositories\EloquentWorldRepository::class
        );
        
        // Shock event repository binding
        $this->app->bind(
            \App\Domains\World\Repositories\ShockEventRepository::class,
            \App\Domains\World\Repositories\EloquentShockEventRepository::class
        );

        // Evolution engine (Runtime → World tick delegation).
        // Closure ensures the implementation is resolved at runtime (avoids "not instantiable" when cache is stale).
        $this->app->bind(
            \App\Domains\World\Contracts\EvolutionEngineInterface::class,
            function ($app) {
                return $app->make(\App\Domains\World\Services\WorldEvolutionEngineAdapter::class);
            }
        );

        // Continuous operation services
        $this->app->singleton(\App\Services\World\ContinuousWorldService::class);
        $this->app->singleton(\App\Domains\Intelligence\Services\WorldIntelligenceService::class);
        $this->app->singleton(\App\Domains\Material\Services\WorldMaterialTracker::class);
        
        // Intelligence repository binding
        $this->app->bind(
            \App\Domains\Intelligence\Repositories\IntelligenceRepository::class,
            \App\Domains\Intelligence\Repositories\EloquentIntelligenceRepository::class
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
        
        // Character repository binding
        $this->app->bind(
            \App\Domains\Character\Repositories\CharacterSurvivalRepository::class,
            \App\Domains\Character\Repositories\EloquentCharacterSurvivalRepository::class
        );
        
        // Material repository binding
        $this->app->bind(
            \App\Domains\Material\Contracts\MaterialRepositoryInterface::class,
            \App\Domains\Material\Repositories\MaterialEloquentRepository::class
        );
        
        // WorldMaterial repository binding
        $this->app->bind(
            \App\Domains\Material\Repositories\WorldMaterialRepository::class,
            \App\Domains\Material\Repositories\WorldMaterialRepository::class
        );
        
        // WorldState repository binding
        $this->app->bind(
            \App\Domains\Material\State\WorldStateRepository::class,
            \App\Domains\Material\State\WorldStateRepository::class
        );
        
        // WorldStateMutator binding
        $this->app->bind(
            \App\Domains\Material\State\WorldStateMutator::class,
            \App\Domains\Material\State\WorldStateMutator::class
        );
        
        // CompressedSnapshot repository binding
        $this->app->bind(
            \App\Domains\Material\State\CompressedSnapshotRepository::class,
            \App\Domains\Material\State\CompressedSnapshotRepository::class
        );
        
        // EntropyCalculator binding
        $this->app->bind(
            \App\Domains\History\Services\EntropyCalculator::class,
            \App\Domains\History\Services\EntropyCalculator::class
        );
        
        // ScarImpactService binding
        $this->app->bind(
            \App\Domains\History\Services\ScarImpactService::class,
            \App\Domains\History\Services\ScarImpactService::class
        );

        // RealityNarrator binding
        $this->app->singleton(\App\Domains\Narrative\Services\RealityNarrator::class);

        // SagaDirector binding
        $this->app->singleton(\App\Domains\Saga\Services\SagaDirector::class);
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
            return \App\Domains\Material\Repositories\MaterialEloquentRepository::exists($value);
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
