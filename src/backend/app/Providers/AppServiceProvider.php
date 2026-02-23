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
            \WorldOS\Legacy\Application\Runtime\Evaluation\UniverseEvaluatorInterface::class,
            function ($app) {
                $driver = config('worldos.evaluator_driver', 'stub');
                if ($driver === 'llm') {
                    return new \WorldOS\Legacy\Application\Runtime\Evaluation\LLMUniverseEvaluator(
                        $app->make(\WorldOS\Legacy\Application\Narrative\LLM\Contracts\LLMProvider::class),
                        new \WorldOS\Legacy\Application\Runtime\Evaluation\StubUniverseEvaluator()
                    );
                }
                return new \WorldOS\Legacy\Application\Runtime\Evaluation\StubUniverseEvaluator();
            }
        );

        // LLM Provider binding
        $this->app->bind(
            \WorldOS\Legacy\Application\Narrative\LLM\Contracts\LLMProvider::class,
            function ($app) {
                $apiKey = config('services.openai.api_key');
                if (empty($apiKey) || app()->environment('testing')) {
                    return new \WorldOS\Legacy\Application\Narrative\LLM\Services\FakeLLMService(
                        $app->make(\WorldOS\Legacy\Application\Narrative\LLM\Support\AIProviderRequestLogger::class)
                    );
                }
                return new \WorldOS\Legacy\Application\Narrative\LLM\Services\OpenAIService(
                    $apiKey,
                    config('services.openai.model'),
                    $app->make(\WorldOS\Legacy\Application\Narrative\LLM\Support\AIProviderRequestLogger::class),
                    $app->make(\App\Services\AI\AIAgentContext::class),
                    $app->make(\App\Services\AI\AIFeatureAgentResolver::class)
                );
            }
        );

        // World system bindings
        $this->app->bind(
            \WorldOS\Blueprint\Domain\Legacy\Contracts\ClaimExtractorInterface::class,
            \WorldOS\Legacy\Application\World\Services\RegexClaimExtractor::class
        );
        
        // World repository binding
        $this->app->bind(
            \WorldOS\Legacy\Infrastructure\World\Repositories\WorldRepository::class,
            \WorldOS\Legacy\Infrastructure\World\Repositories\EloquentWorldRepository::class
        );

        $this->app->bind(
            \App\Domains\World\Repositories\WorldRepository::class,
            \App\Domains\World\Repositories\EloquentWorldRepository::class
        );

        // Tuzy: World repository (DDD port)
        $this->app->bind(
            \WorldOS\Blueprint\Domain\Legacy\Repository\WorldRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\Persistence\World\EloquentWorldRepository::class
        );

        // Tuzy: Universe repository (Runtime context)
        $this->app->bind(
            \WorldOS\Legacy\Domain\Runtime\Repository\UniverseRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\Persistence\Runtime\EloquentUniverseRepository::class
        );

        // Tuzy: Saga repository
        $this->app->bind(
            \WorldOS\Saga\Domain\Legacy\Repository\SagaRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\Persistence\Saga\EloquentSagaRepository::class
        );

        // Tuzy: UniverseStyle repository (Cosmology)
        $this->app->bind(
            \WorldOS\Legacy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\Persistence\Cosmology\EloquentUniverseStyleRepository::class
        );

        // Tuzy: EvolutionProfile repository (Evolution)
        $this->app->bind(
            \WorldOS\Evolution\Domain\Legacy\Repository\EvolutionProfileRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\Persistence\Evolution\EloquentEvolutionProfileRepository::class
        );

        // Tuzy: NarrativeSeries repository (Narrative)
        $this->app->bind(
            \WorldOS\Saga\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\Persistence\Narrative\EloquentNarrativeSeriesRepository::class
        );

        // Tuzy: Hero repository (Vietnamese)
        $this->app->bind(
            \WorldOS\Saga\Domain\Hero\Repository\HeroRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\Persistence\Heroes\EloquentHeroRepository::class
        );

        // Shock event repository binding
        $this->app->bind(
            \WorldOS\Legacy\Infrastructure\World\Repositories\ShockEventRepository::class,
            \WorldOS\Legacy\Infrastructure\World\Repositories\EloquentShockEventRepository::class
        );

        // Evolution engine (Runtime → World tick delegation).
        // Closure ensures the implementation is resolved at runtime (avoids "not instantiable" when cache is stale).
        $this->app->bind(
            \WorldOS\Blueprint\Domain\Legacy\Contracts\EvolutionEngineInterface::class,
            function ($app) {
                return $app->make(\WorldOS\Legacy\Application\World\Services\WorldEvolutionEngineAdapter::class);
            }
        );

        // Explicitly bind WorldEvolutionKernel to inject BasePhysicsEngine
        $this->app->bind(
            \WorldOS\Legacy\Application\Evolution\Kernel\WorldEvolutionKernel::class,
            function ($app) {
                return new \WorldOS\Legacy\Application\Evolution\Kernel\WorldEvolutionKernel(
                    $app->make(\WorldOS\Legacy\Application\Evolution\Engine\VectorDynamicsEngine::class),
                    $app->make(\WorldOS\Legacy\Application\Evolution\Kernel\StateLoader::class),
                    $app->make(\WorldOS\Legacy\Application\Cosmology\Services\BasePhysicsEngine::class),
                    $app->make(\WorldOS\Legacy\Application\Cosmology\Services\StructuralMutationEngine::class),
                    $app->make(\WorldOS\Legacy\Domain\Material\MaterialWorldBridge::class)
                );
            }
        );

        // Continuous operation services
        $this->app->singleton(\WorldOS\Legacy\Application\Intelligence\Services\WorldIntelligenceService::class);
        $this->app->singleton(\WorldOS\Legacy\Application\Material\Services\WorldMaterialTracker::class);
        
        // Intelligence repository binding
        $this->app->bind(
            \WorldOS\Legacy\Infrastructure\Intelligence\Repositories\IntelligenceRepository::class,
            \WorldOS\Legacy\Infrastructure\Intelligence\Repositories\EloquentIntelligenceRepository::class
        );

        // Phase 31: History & Institution Domains
        $this->app->bind(
            \WorldOS\Legacy\Infrastructure\History\Repositories\ScarRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\History\Repositories\ScarEloquentRepository::class
        );
        $this->app->bind(
            \WorldOS\Legacy\Infrastructure\History\Repositories\MythRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\History\Repositories\MythEloquentRepository::class
        );
        $this->app->bind(
            \WorldOS\Legacy\Infrastructure\Institution\Repositories\InstitutionRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\Institution\Repositories\InstitutionEloquentRepository::class
        );
        
        // Character repository binding
        $this->app->bind(
            \WorldOS\Legacy\Infrastructure\Character\Repositories\CharacterSurvivalRepository::class,
            \WorldOS\Legacy\Infrastructure\Character\Repositories\EloquentCharacterSurvivalRepository::class
        );

        $this->app->bind(
            \App\Domains\Character\Repositories\CharacterSurvivalRepository::class,
            \App\Domains\Character\Repositories\EloquentCharacterSurvivalRepository::class
        );
        
        // Material repository binding
        $this->app->bind(
            \WorldOS\Legacy\Domain\Material\Contracts\MaterialRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\Material\Repositories\MaterialEloquentRepository::class
        );
        
        // WorldMaterial repository binding
        $this->app->bind(
            \WorldOS\Legacy\Infrastructure\Material\Repositories\WorldMaterialRepository::class,
            \WorldOS\Legacy\Infrastructure\Material\Repositories\WorldMaterialRepository::class
        );
        
        // WorldState repository binding
        $this->app->bind(
            \WorldOS\Legacy\Application\Material\State\WorldStateRepository::class,
            \WorldOS\Legacy\Application\Material\State\WorldStateRepository::class
        );
        
        // WorldStateMutator binding
        $this->app->bind(
            \WorldOS\Legacy\Application\Material\State\WorldStateMutator::class,
            \WorldOS\Legacy\Application\Material\State\WorldStateMutator::class
        );
        
        // CompressedSnapshot repository binding
        $this->app->bind(
            \WorldOS\Legacy\Application\Material\State\CompressedSnapshotRepository::class,
            \WorldOS\Legacy\Application\Material\State\CompressedSnapshotRepository::class
        );
        
        // EntropyCalculator binding
        $this->app->bind(
            \WorldOS\Legacy\Application\History\Services\EntropyCalculator::class,
            \WorldOS\Legacy\Application\History\Services\EntropyCalculator::class
        );
        
        // ScarImpactService binding
        $this->app->bind(
            \WorldOS\Legacy\Application\History\Services\ScarImpactService::class,
            \WorldOS\Legacy\Application\History\Services\ScarImpactService::class
        );

        // RealityNarrator binding
        $this->app->singleton(\WorldOS\Legacy\Application\Narrative\Services\RealityNarrator::class);

        // SagaDirector binding
        $this->app->singleton(\WorldOS\Legacy\Application\Saga\Services\SagaDirector::class);

        // Cosmology Bindings (Consolidated from Cosmic)
        $this->app->bind(
            \WorldOS\Legacy\Domain\Cosmology\Contracts\CosmicSnapshotRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\Cosmology\Repositories\CosmicSnapshotEloquentRepository::class
        );
        $this->app->bind(
            \WorldOS\Legacy\Domain\Cosmology\Contracts\AttractorRepositoryInterface::class,
            \WorldOS\Legacy\Infrastructure\Cosmology\Repositories\AttractorEloquentRepository::class
        );

        // Phase 15: Cosmos Domain (Meta-Simulation)
        $this->app->singleton(\WorldOS\Legacy\Domain\Cosmos\Service\ParetoSelector::class);
        $this->app->singleton(\WorldOS\Legacy\Domain\Cosmos\Service\ObjectiveEngine::class, function ($app) {
            return new \WorldOS\Legacy\Domain\Cosmos\Service\ObjectiveEngine(
                $app->make(\WorldOS\Legacy\Domain\Cosmos\Implementation\NarrativeDramaObjective::class)
            );
        });
        $this->app->bind(\WorldOS\Legacy\Domain\Cosmos\Contracts\Objective::class, \WorldOS\Legacy\Domain\Cosmos\Implementation\NarrativeDramaObjective::class);

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
            return \WorldOS\Legacy\Infrastructure\World\Repositories\WorldRepository::exists($value);
        });

        Validator::extend('material_id', function ($attribute, $value, $parameters, $validator) {
            return \WorldOS\Legacy\Infrastructure\Material\Repositories\MaterialEloquentRepository::exists($value);
        });

        Validator::extend('character_id', function ($attribute, $value, $parameters, $validator) {
            return \WorldOS\Legacy\Infrastructure\Character\Repositories\CharacterSurvivalRepository::exists($value);
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
