<?php

namespace App\Domains\Saga\Services;

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Domains\Cosmology\Repositories\UniverseSnapshotRepository;
use App\Domains\Runtime\Evaluation\DecisionEngine;
use App\Domains\Runtime\Evaluation\MetricsExtractor;
use App\Domains\Runtime\Evaluation\UniverseEvaluatorInterface;
use App\Domains\Runtime\UniverseRuntimeService;
use App\Models\UniverseModel;
use App\Models\World;
use Illuminate\Support\Str;
use App\Domains\Saga\Saga;
use App\Domains\Saga\SagaWorld;
use App\Domains\Saga\Services\GenesisPresetService;

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
    public function genesisV3(Saga $saga, int $ticksPerUniverse = 10): void
    {
        $presetKey = $saga->metadata['genesis_preset'] ?? $saga->metadata['preset_key'] ?? 'cuu_trong_thien';
        $preset = app(GenesisPresetService::class)->find($presetKey) ?? [];
        $baseName = $saga->name . ' - World 1';
        $name = $baseName;
        $counter = 1;
        while (World::where('name', $name)->exists()) {
            $name = "{$baseName} ({$counter})";
            $counter++;
        }

        $world = World::create([
            'name' => $name,
            'status' => 'active',
            'tick' => 0,
            'autonomous' => true,
            'preset' => $presetKey,
            'gene_vector' => $preset['gene_vector'] ?? [],
            'origin_type' => $saga->metadata['origin_type'] ?? 'cosmic',
            'genre' => $preset['genre'] ?? $saga->genre ?? 'historical',
            'config' => [
                'preset_key' => $presetKey,
                'current_stage' => $preset['power_stage'] ?? 'mundane',
                'archetype' => $preset['archetype'] ?? 'BALANCED',
            ],
        ]);

        if (!empty($preset)) {
            app(\App\Domains\World\Services\WorldPowerProfileService::class)->bootstrapProfile($world, $preset);
        }

        $universe = $this->spawnUniverse($world, null);
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
