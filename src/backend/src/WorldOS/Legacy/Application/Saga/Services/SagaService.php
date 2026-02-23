<?php

namespace WorldOS\Legacy\Application\Saga\Services;

use WorldOS\Legacy\Application\Cosmology\Entities\Universe;
use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Legacy\Infrastructure\Cosmology\Repositories\CosmologyRepository;
use WorldOS\Legacy\Infrastructure\Cosmology\Repositories\UniverseSnapshotRepository;
use WorldOS\Legacy\Application\Runtime\Evaluation\DecisionEngine;
use WorldOS\Legacy\Application\Runtime\Evaluation\MetricsExtractor;
use WorldOS\Legacy\Application\Runtime\Evaluation\UniverseEvaluatorInterface;
use WorldOS\Legacy\Domain\Runtime\UniverseRuntimeService;
use App\Models\UniverseModel;
use App\Models\World;
use Illuminate\Support\Str;
use WorldOS\Saga\Domain\Legacy\Saga;
use WorldOS\Saga\Domain\Legacy\SagaWorld;
use WorldOS\Legacy\Application\Saga\Services\GenesisPresetService;

/**
 * WorldOS v3: Saga as orchestrator. Spawn/advance/evaluate/fork Universe only.
 * Does not tick World; does not call SagaRunner.simulateWorld.
 */
class SagaService
{
    public function __construct(
        private CosmologyRepository $cosmologyRepository,
        private UniverseSnapshotRepository $universeSnapshotRepository,
        private UniverseRuntimeService $runtimeService,
        private MetricsExtractor $metricsExtractor,
        private UniverseEvaluatorInterface $evaluator,
        private DecisionEngine $decisionEngine
    ) {
    }

    /**
     * Create one Universe from a World (initial state from World/archetype). Writes first snapshot at tick 0.
     */
    public function spawnUniverse(World $world, ?string $parentUniverseId = null): Universe
    {
        $id = (string) Str::uuid();
        $archetype = $world->config['archetype'] ?? $world->gene_vector['archetype'] ?? 'BALANCED';
        $universe = $this->cosmologyRepository->createCustom($id, [
            'world_id' => $world->id,
            'archetype' => is_string($archetype) ? $archetype : 'BALANCED',
            'name' => $world->name . ' — Universe ' . substr($id, 0, 8),
        ]);

        $model = UniverseModel::find($id);
        if ($model) {
            $model->parent_universe_id = $parentUniverseId;
            $model->status = 'running';
            $model->save();
        }

        $this->universeSnapshotRepository->save($universe, []);
        return $universe;
    }

    /**
     * Advance each universe owned by the saga by N ticks. Uses UniverseRuntimeService only.
     */
    public function runBatch(Saga $saga, int $ticksPerUniverse): void
    {
        $sagaWorlds = $saga->sagaWorlds()->whereNotNull('universe_id')->orderBy('sequence')->get();
        foreach ($sagaWorlds as $sw) {
            if ($sw->universe_id && $sw->status !== SagaWorld::STATUS_COLLAPSED) {
                $sw->update(['status' => SagaWorld::STATUS_RUNNING]);
                $this->runtimeService->advance($sw->universe_id, $ticksPerUniverse);
            }
        }
    }

    /**
     * Evaluate universe (stub: Phase 3 will plug AI). Returns recommendation: continue | fork | archive.
     */
    public function evaluate(Universe $universe): string
    {
        return 'continue';
    }

    /**
     * Clone universe from snapshot at given tick; new universe has parent_universe_id set.
     */
    public function fork(Universe $universe, int $fromTick): Universe
    {
        $snapshot = $this->universeSnapshotRepository->getAtTick($universe->getId(), $fromTick);
        if (!$snapshot) {
            throw new \InvalidArgumentException("No snapshot for universe {$universe->getId()} at tick {$fromTick}");
        }

        $model = UniverseModel::find($universe->getId());
        $worldId = $model ? $model->world_id : null;
        if (!$worldId) {
            throw new \InvalidArgumentException('Universe must have world_id to fork.');
        }

        $state = WorldStateVector::fromArray($snapshot->state_vector);
        $params = $model ? ($model->parameters ?? []) : [];
        $newId = (string) Str::uuid();
        $forked = new Universe(
            $state,
            $params,
            $newId,
            $fromTick,
            $model->coords ?? null,
            $model->cosmic_faction_id ?? null
        );

        $this->cosmologyRepository->save($forked, $worldId);

        $newModel = UniverseModel::find($newId);
        if ($newModel) {
            $newModel->parent_universe_id = $universe->getId();
            $newModel->status = 'running';
            $newModel->save();
        }

        $this->universeSnapshotRepository->save($forked, []);
        return $forked;
    }

    /**
     * WorldOS v3 Genesis: create one World, spawn one Universe, link to Saga, then run batch.
     * Does not dispatch RunSagaSimulationJob or call SagaRunner.
     */
    /**
     * Create a World Container (Physical/Meta rules only, no Universe yet).
     */
    public function createWorldContainer(string $name, array $config): World
    {
        // Enforce unique name if needed, or just append timestamp
        $baseName = $name;
        $counter = 1;
        while (World::where('name', $name)->exists()) {
            $name = "{$baseName} ({$counter})";
            $counter++;
        }

        return World::create([
            'name' => $name,
            'status' => 'active',
            'tick' => 0,
            'autonomous' => true,
            'preset' => null, // World container doesn't use preset in V3 split flow
            'gene_vector' => [],
            'origin_type' => $config['origin_type'] ?? 'cosmic',
            'genre' => $config['genre'] ?? 'historical',
            'config' => $config, // Store raw config like physics profile headers
        ]);
    }

    /**
     * Spawn a Universe from a Preset within a World.
     */
    public function spawnUniverseFromPreset(World $world, string $presetKey): Universe
    {
        $preset = app(GenesisPresetService::class)->find($presetKey) ?? [];
        
        // Apply preset config to World if it's the first universe (optional, but good for consistency)
        if (!empty($preset)) {
            $worldConfig = is_array($world->config) ? $world->config : [];
            $worldConfig['preset_key'] = $presetKey;
            $worldConfig['archetype'] = $preset['archetype'] ?? null;
            $worldConfig['seed_vector'] = $preset['seed_vector'] ?? null;
            $worldConfig['drift_profile'] = $preset['drift_profile'] ?? null;
            $world->config = $worldConfig;

            $geneVector = is_array($world->gene_vector) ? $world->gene_vector : [];
            $geneVector['archetype'] = $preset['archetype'] ?? null;
            $geneVector['seed_vector'] = $preset['seed_vector'] ?? null;
            $world->gene_vector = $geneVector;
            $world->save();

            app(\WorldOS\Legacy\Application\World\Services\WorldPowerProfileService::class)->bootstrapProfile($world, $preset);
        }

        return $this->spawnUniverse($world, null);
    }

    /**
     * WorldOS v3 Genesis: (Legacy Wrapper)
     */
    public function genesisV3(Saga $saga, int $ticksPerUniverse = 10): void
    {
        $presetKey = $saga->metadata['genesis_preset'] ?? $saga->metadata['preset_key'] ?? 'cuu_trong_thien';
        $baseName = $saga->name . ' - World 1';
        
        $worldConfig = [
            'origin_type' => $saga->metadata['origin_type'] ?? 'cosmic',
            'genre' => $saga->genre ?? 'historical',
        ];

        $world = $this->createWorldContainer($baseName, $worldConfig);
        $universe = $this->spawnUniverseFromPreset($world, $presetKey);

        SagaWorld::create([
            'saga_id' => $saga->id,
            'world_id' => $world->id,
            'universe_id' => $universe->getId(),
            'sequence' => 1,
            'status' => SagaWorld::STATUS_RUNNING,
        ]);

        $saga->update(['current_universe_id' => $universe->getId(), 'status' => Saga::STATUS_RUNNING]);
        $this->runBatch($saga, $ticksPerUniverse);
    }

    /**
     * Advance each universe by N ticks, then evaluate and run decision (fork/archive/continue) once per universe.
     */
    public function runBatchWithEvaluation(Saga $saga, int $ticksPerUniverse, int $evaluateEveryTicks = 10): void
    {
        $this->runBatch($saga, $ticksPerUniverse);
        $sagaWorlds = $saga->sagaWorlds()->whereNotNull('universe_id')->orderBy('sequence')->get();
        foreach ($sagaWorlds as $sw) {
            if (!$sw->universe_id || $sw->status === SagaWorld::STATUS_COLLAPSED) {
                continue;
            }
            $metrics = $this->metricsExtractor->fromLatestSnapshot($sw->universe_id);
            if ($metrics === null) {
                continue;
            }
            $result = $this->evaluator->evaluate($metrics);
            $universe = $this->cosmologyRepository->find($sw->universe_id);
            if ($universe !== null) {
                $decision = $this->decisionEngine->execute($universe, $result);
                if ($decision === 'fork') {
                    $snapshot = $this->universeSnapshotRepository->getLatest($universe->getId());
                    $fromTick = $snapshot ? $snapshot->tick : 0;
                    $this->fork($universe, $fromTick);
                }
            }
        }
    }
}
