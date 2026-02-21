<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Domains\Saga\Repositories\SagaRepository;
use App\Domains\Saga\Actions\CreateSagaAction;
use App\Domains\Saga\Actions\AdvanceSagaAction;
use App\Domains\Saga\Services\SagaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tuzy\Domain\Runtime\Exception\UniverseNotFoundException;

class WriterSagaController extends Controller
{
    public function __construct(
        private SagaRepository $repository,
        private CreateSagaAction $createSagaAction,
        private AdvanceSagaAction $advanceSagaAction,
        private SagaService $sagaService
    ) {}

    /**
     * GET /api/writer/sagas — list all sagas.
     */
    public function index(): JsonResponse
    {
        $sagas = $this->repository->getAllWithStatus()->map(fn($s) => [
            'id'                   => (string) $s->id,
            'name'                 => $s->name,
            'status'               => $s->status,
            'saga_worlds_count'    => $s->saga_worlds_count,
            'world_count'          => $s->world_count,
            'current_universe_id'   => $s->current_universe_id ? (string) $s->current_universe_id : null,
            'created_at'            => $s->created_at?->toIso8601String(),
            'updated_at'            => $s->updated_at?->toIso8601String(),
        ]);

        return response()->json($sagas);
    }

    /**
     * GET /api/writer/sagas/stats — global metrics.
     */
    public function getStats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->repository->getGlobalStats(),
        ]);
    }

    /**
     * GET /api/writer/sagas/{sagaId} — detail.
     */
    public function show(string $sagaId): JsonResponse
    {
        $saga = $this->repository->findById($sagaId);
        $sagaWorlds = $this->repository->getSagaWorlds($sagaId);

        $sagaWorldsPayload = $sagaWorlds->map(function ($sw) {
            $u = $sw->universe;
            return [
                'id'             => (string) $sw->id,
                'world_id'       => $sw->world_id ? (string) $sw->world_id : null,
                'world_name'     => $sw->world?->name ?? ('World #' . $sw->sequence),
                'universe_id'    => $sw->universe_id ? (string) $sw->universe_id : null,
                'sequence'       => $sw->sequence,
                'status'         => $sw->status,
                'universe_age'   => $u ? (int) $u->age : null,
                'universe_entropy' => $u !== null && $u->entropy !== null ? (float) $u->entropy : null,
                'universe_stability_index' => $u !== null && $u->stability_index !== null ? (float) $u->stability_index : null,
                'universe_status' => $u ? (string) $u->status : null,
            ];
        });

        return response()->json([
            'id'                   => (string) $saga->id,
            'name'                 => $saga->name,
            'status'               => $saga->status,
            'world_count'          => $saga->world_count,
            'current_universe_id'  => $saga->current_universe_id ? (string) $saga->current_universe_id : null,
            'created_at'            => $saga->created_at?->toIso8601String(),
            'updated_at'            => $saga->updated_at?->toIso8601String(),
            'saga_worlds'           => $sagaWorldsPayload->values()->all(),
        ]);
    }

    /**
     * POST /api/writer/sagas/create-from-active.
     * WorldOS v3: Saga orchestrates an EXISTING Universe (from Genesis).
     */
    public function createFromActive(Request $request): JsonResponse
    {
        // 1. Resolve Universe (from request or latest orphan)
        $universeId = $request->input('universe_id');
        
        if ($universeId) {
            $universe = \App\Models\UniverseModel::find($universeId);
        } else {
            // "Active" heuristics: Latest universe created.
            // Ideally should filter out those already in a Saga, but for now taking latest is safe for single-user flow.
            $universe = \App\Models\UniverseModel::orderBy('created_at', 'desc')->first();
        }

        if ($universeId && !$universe) {
            throw UniverseNotFoundException::withId($universeId);
        }
        if (!$universe) {
            return response()->json([
                'error' => 'No active Universe found. Please run Genesis first.',
                'hint' => 'Saga acts as a container for a valid Universe.'
            ], 422);
        }

        // 2. Create Saga Container
        $saga = $this->createSagaAction->execute(
            'Saga of ' . $universe->name,
            5 // Default world count target
        );

        // 3. Attach Universe to Saga (SagaWorld)
        // V3: Saga manages this Universe.
        $sagaWorld = new \App\Domains\Saga\SagaWorld([
            'id' => \Illuminate\Support\Str::uuid(),
            'saga_id' => $saga->id,
            'universe_id' => $universe->id,
            'world_id' => $universe->world_id, // Link to the template used
            'sequence' => 1,
            'status' => \App\Domains\Saga\SagaWorld::STATUS_RUNNING,
        ]);
        $sagaWorld->save();

        // 4. Update Saga Context
        $saga->current_universe_id = $universe->id;
        $saga->status = \App\Domains\Saga\Saga::STATUS_RUNNING;
        $saga->genre = $universe->world?->genre ?? 'unknown';
        $saga->save();

        return response()->json([
            'id' => $saga->id,
            'name' => $saga->name,
            'status' => $saga->status,
            'message' => "Saga initialized from Universe: {$universe->name}",
            'universe_id' => $universe->id
        ], 201);
    }

    /**
     * GET /api/writer/saga/{sagaId}/tree.
     */
    public function tree(string $sagaId): JsonResponse
    {
        $saga = $this->repository->findById($sagaId);
        $nodes = $this->repository->getSagaWorlds($sagaId)->map(function ($sw) {
            $w = $sw->world;
            $u = $sw->universe;
            
            $nodeId = (string) ($sw->universe_id ?? ($w?->id ?? $sw->id));
            $parentId = $u ? (string) $u->parent_universe_id : ($w?->parent_id ? (string) $w->parent_id : null);
            
            return [
                'id'                   => $nodeId,
                'parentId'             => $parentId,
                'name'                 => $u?->name ?? ($w?->name ?? ('World #' . $sw->sequence)),
                'current_era'          => $u ? (string) floor($u->age / 50) : ($w ? (string) floor(($w->current_time ?? 0) / 50) : '0'),
                'status'               => $sw->status,
                'has_collapsed'        => $sw->hasCollapsed(),
                'sequence'             => $sw->sequence,
                'universe_id'          => $sw->universe_id ? (string) $sw->universe_id : null,
                'age'                  => $u ? (int) $u->age : null,
                'universe_status'      => $u ? (string) $u->status : null,
            ];
        });

        return response()->json(['nodes' => $nodes]);
    }

    /**
     * POST /api/writer/saga/{sagaId}/advance.
     */
    public function advance(Request $request, string $sagaId): JsonResponse
    {
        $saga = $this->repository->findById($sagaId);
        $ticks = (int) $request->input('ticks', 10);
        
        try {
            $this->advanceSagaAction->execute($saga, $ticks);
            return response()->json([
                'success' => true,
                'message' => "Advanced {$ticks} tick(s).",
                'ticks'   => $ticks,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/writer/saga/{sagaId}/run.
     * WorldOS v3: Uses SagaService (Universe-centric) instead of legacy SagaRunner.
     */
    public function run(Request $request, string $sagaId): JsonResponse
    {
        $saga = $this->repository->findById($sagaId);
        if ($saga->isComplete()) {
            return response()->json(['error' => 'Saga already completed.'], 422);
        }

        $ticks = (int) $request->input('ticks', 10);

        try {
            $this->sagaService->runBatchWithEvaluation($saga, $ticks);
            return response()->json([
                'success' => true,
                'message' => "Simulation completed: {$ticks} tick(s) advanced via V3 pipeline.",
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
