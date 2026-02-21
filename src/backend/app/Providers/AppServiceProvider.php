<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Listeners\TuzyCreatedEventSubscriber;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // WorldOS v3: Universe evaluator (Phase 3) — driver via WORLDOS_EVALUATOR_DRIVER
        $this->app->bind(
            \Tuzy\Application\Runtime\Evaluation\UniverseEvaluatorInterface::class,
            function ($app) {
                $driver = config('worldos.evaluator_driver', 'stub');
                if ($driver === 'llm') {
                    return new \Tuzy\Application\Runtime\Evaluation\LLMUniverseEvaluator(
                        $app->make(\Tuzy\Application\Narrative\LLM\Contracts\LLMProvider::class),
                        new \Tuzy\Application\Runtime\Evaluation\StubUniverseEvaluator()
                    );
                }
                return new \Tuzy\Application\Runtime\Evaluation\StubUniverseEvaluator();
            }
        );

        // LLM Provider binding
        $this->app->bind(
            \Tuzy\Application\Narrative\LLM\Contracts\LLMProvider::class,
            function ($app) {
                $apiKey = config('services.openai.api_key');
                if (empty($apiKey) || app()->environment('testing')) {
                    return new \Tuzy\Application\Narrative\LLM\Services\FakeLLMService(
                        $app->make(\Tuzy\Application\Narrative\LLM\Support\AIProviderRequestLogger::class)
                    );
                }
                return new \Tuzy\Application\Narrative\LLM\Services\OpenAIService(
                    $apiKey,
                    config('services.openai.model'),
                    $app->make(\Tuzy\Application\Narrative\LLM\Support\AIProviderRequestLogger::class),
                    $app->make(\App\Services\AI\AIAgentContext::class),
                    $app->make(\App\Services\AI\AIFeatureAgentResolver::class)
                );
            }
        );

        // World system bindings
        $this->app->bind(
            \Tuzy\Domain\World\Contracts\ClaimExtractorInterface::class,
            \Tuzy\Application\World\Services\RegexClaimExtractor::class
        );
        
        // World repository binding
        $this->app->bind(
            \Tuzy\Infrastructure\World\Repositories\WorldRepository::class,
            \Tuzy\Infrastructure\World\Repositories\EloquentWorldRepository::class
        );

        // Tuzy: World repository (DDD port)
        $this->app->bind(
            \Tuzy\Domain\World\Repository\WorldRepositoryInterface::class,
            \Tuzy\Infrastructure\Persistence\World\EloquentWorldRepository::class
        );

        // Tuzy: Universe repository (Runtime context)
        $this->app->bind(
            \Tuzy\Domain\Runtime\Repository\UniverseRepositoryInterface::class,
            \Tuzy\Infrastructure\Persistence\Runtime\EloquentUniverseRepository::class
        );

        // Tuzy: Saga repository
        $this->app->bind(
            \Tuzy\Domain\Saga\Repository\SagaRepositoryInterface::class,
            \Tuzy\Infrastructure\Persistence\Saga\EloquentSagaRepository::class
        );

        // Tuzy: UniverseStyle repository (Cosmology)
        $this->app->bind(
            \Tuzy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface::class,
            \Tuzy\Infrastructure\Persistence\Cosmology\EloquentUniverseStyleRepository::class
        );

        // Tuzy: EvolutionProfile repository (Evolution)
        $this->app->bind(
            \Tuzy\Domain\Evolution\Repository\EvolutionProfileRepositoryInterface::class,
            \Tuzy\Infrastructure\Persistence\Evolution\EloquentEvolutionProfileRepository::class
        );

        // Tuzy: NarrativeSeries repository (Narrative)
        $this->app->bind(
            \Tuzy\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface::class,
            \Tuzy\Infrastructure\Persistence\Narrative\EloquentNarrativeSeriesRepository::class
        );

        // Tuzy: WorldHero repository (Vietnamese)
        $this->app->bind(
            \Tuzy\Domain\Heroes\Repository\WorldHeroRepositoryInterface::class,
            \Tuzy\Infrastructure\Persistence\Heroes\EloquentWorldHeroRepository::class
        );

        // Shock event repository binding
        $this->app->bind(
            \Tuzy\Infrastructure\World\Repositories\ShockEventRepository::class,
            \Tuzy\Infrastructure\World\Repositories\EloquentShockEventRepository::class
        );

        // Evolution engine (Runtime → World tick delegation).
        // Closure ensures the implementation is resolved at runtime (avoids "not instantiable" when cache is stale).
        $this->app->bind(
            \Tuzy\Domain\World\Contracts\EvolutionEngineInterface::class,
            function ($app) {
                return $app->make(\Tuzy\Application\World\Services\WorldEvolutionEngineAdapter::class);
            }
        );

        // Explicitly bind WorldEvolutionKernel to inject BasePhysicsEngine
        $this->app->bind(
            \Tuzy\Application\Evolution\Kernel\WorldEvolutionKernel::class,
            function ($app) {
                return new \Tuzy\Application\Evolution\Kernel\WorldEvolutionKernel(
                    $app->make(\Tuzy\Application\Evolution\Engine\VectorDynamicsEngine::class),
                    $app->make(\Tuzy\Application\Evolution\Kernel\StateLoader::class),
                    $app->make(\Tuzy\Application\Cosmology\Services\BasePhysicsEngine::class),
                    $app->make(\Tuzy\Application\Cosmology\Services\StructuralMutationEngine::class),
                    $app->make(\Tuzy\Domain\Material\MaterialWorldBridge::class)
                );
            }
        );

        // Continuous operation services
        $this->app->singleton(\App\Services\World\ContinuousWorldService::class);
        $this->app->singleton(\Tuzy\Application\Intelligence\Services\WorldIntelligenceService::class);
        $this->app->singleton(\Tuzy\Application\Material\Services\WorldMaterialTracker::class);
        
        // Intelligence repository binding
        $this->app->bind(
            \Tuzy\Infrastructure\Intelligence\Repositories\IntelligenceRepository::class,
            \Tuzy\Infrastructure\Intelligence\Repositories\EloquentIntelligenceRepository::class
        );

        // Phase 31: History & Institution Domains
        $this->app->bind(
            \Tuzy\Infrastructure\History\Repositories\ScarRepositoryInterface::class,
            \Tuzy\Infrastructure\History\Repositories\ScarEloquentRepository::class
        );
        $this->app->bind(
            \Tuzy\Infrastructure\History\Repositories\MythRepositoryInterface::class,
            \Tuzy\Infrastructure\History\Repositories\MythEloquentRepository::class
        );
        $this->app->bind(
            \Tuzy\Infrastructure\Institution\Repositories\InstitutionRepositoryInterface::class,
            \Tuzy\Infrastructure\Institution\Repositories\InstitutionEloquentRepository::class
        );
        
        // Character repository binding
        $this->app->bind(
            \Tuzy\Infrastructure\Character\Repositories\CharacterSurvivalRepository::class,
            \Tuzy\Infrastructure\Character\Repositories\EloquentCharacterSurvivalRepository::class
        );
        
        // Material repository binding
        $this->app->bind(
            \Tuzy\Domain\Material\Contracts\MaterialRepositoryInterface::class,
            \Tuzy\Infrastructure\Material\Repositories\MaterialEloquentRepository::class
        );
        
        // WorldMaterial repository binding
        $this->app->bind(
            \Tuzy\Infrastructure\Material\Repositories\WorldMaterialRepository::class,
            \Tuzy\Infrastructure\Material\Repositories\WorldMaterialRepository::class
        );
        
        // WorldState repository binding
        $this->app->bind(
            \Tuzy\Application\Material\State\WorldStateRepository::class,
            \Tuzy\Application\Material\State\WorldStateRepository::class
        );
        
        // WorldStateMutator binding
        $this->app->bind(
            \Tuzy\Application\Material\State\WorldStateMutator::class,
            \Tuzy\Application\Material\State\WorldStateMutator::class
        );
        
        // CompressedSnapshot repository binding
        $this->app->bind(
            \Tuzy\Application\Material\State\CompressedSnapshotRepository::class,
            \Tuzy\Application\Material\State\CompressedSnapshotRepository::class
        );
        
        // EntropyCalculator binding
        $this->app->bind(
            \Tuzy\Application\History\Services\EntropyCalculator::class,
            \Tuzy\Application\History\Services\EntropyCalculator::class
        );
        
        // ScarImpactService binding
        $this->app->bind(
            \Tuzy\Application\History\Services\ScarImpactService::class,
            \Tuzy\Application\History\Services\ScarImpactService::class
        );

        // RealityNarrator binding
        $this->app->singleton(\Tuzy\Application\Narrative\Services\RealityNarrator::class);

        // SagaDirector binding
        $this->app->singleton(\Tuzy\Application\Saga\Services\SagaDirector::class);

        // Cosmology Bindings (Consolidated from Cosmic)
        $this->app->bind(
            \Tuzy\Domain\Cosmology\Contracts\CosmicSnapshotRepositoryInterface::class,
            \Tuzy\Infrastructure\Cosmology\Repositories\CosmicSnapshotEloquentRepository::class
        );
        $this->app->bind(
            \Tuzy\Domain\Cosmology\Contracts\AttractorRepositoryInterface::class,
            \Tuzy\Infrastructure\Cosmology\Repositories\AttractorEloquentRepository::class
        );

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::subscribe(TuzyCreatedEventSubscriber::class);

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
            return \Tuzy\Infrastructure\World\Repositories\WorldRepository::exists($value);
        });

        Validator::extend('material_id', function ($attribute, $value, $parameters, $validator) {
            return \Tuzy\Infrastructure\Material\Repositories\MaterialEloquentRepository::exists($value);
        });

        Validator::extend('character_id', function ($attribute, $value, $parameters, $validator) {
            return \Tuzy\Infrastructure\Character\Repositories\CharacterSurvivalRepository::exists($value);
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
