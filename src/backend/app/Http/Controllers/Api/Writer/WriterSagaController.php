<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Domains\Saga\Saga;
use App\Domains\Saga\Services\SagaService;
use App\Jobs\RunSagaSimulationJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Writer API: Saga management (list, show, tree, run, advance).
 */
class WriterSagaController extends Controller
{
    public function __construct(
        private SagaService $sagaService
    ) {
    }

    /**
     * GET /api/writer/sagas — list all sagas with world counts and current_universe_id.
     */
    public function index(): JsonResponse
    {
        $sagas = Saga::withCount('sagaWorlds')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn(Saga $s) => [
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
     * GET /api/writer/sagas/{sagaId} — saga detail with saga_worlds and universe summary.
     */
    public function show(string $sagaId): JsonResponse
    {
        $saga = Saga::findOrFail($sagaId);
        $sagaWorlds = $saga->sagaWorlds()->with(['world', 'universe'])->orderBy('sequence')->get();

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
     * POST /api/writer/sagas/create-from-active — create saga from active worlds.
     */
    public function createFromActive(): JsonResponse
    {
        $saga = Saga::create([
            'name'        => 'Saga ' . now()->format('Y-m-d H:i'),
            'world_count' => 5,
            'status'      => Saga::STATUS_PENDING,
        ]);

        return response()->json([
            'id'      => $saga->id,
            'name'    => $saga->name,
            'status'  => $saga->status,
            'message' => 'Saga created.',
        ], 201);
    }

    /**
     * GET /api/writer/saga/{sagaId}/tree — Yggdrasil tree data (v3: include universe_id, age, status).
     */
    public function tree(string $sagaId): JsonResponse
    {
        $saga = Saga::findOrFail($sagaId);
        $nodes = $saga->sagaWorlds()->with(['world', 'universe'])->get()->map(function ($sw) {
            $w = $sw->world;
            $u = $sw->universe;
            return [
                'id'                   => (string) ($w?->id ?? $sw->id),
                'parentId'             => $w?->parent_id !== null ? (string) $w->parent_id : null,
                'name'                 => $w?->name ?? ('World #' . $sw->sequence),
                'current_era'          => $w ? (string) floor(($w->current_time ?? 0) / 50) : ($u ? (string) $u->age : '0'),
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
     * POST /api/writer/saga/{sagaId}/advance — advance each universe by N ticks (v3 runBatch).
     */
    public function advance(Request $request, string $sagaId): JsonResponse
    {
        $saga = Saga::findOrFail($sagaId);
        if ($saga->isComplete()) {
            return response()->json(['error' => 'Saga already completed.'], 422);
        }

        $ticks = (int) $request->input('ticks', 10);
        $ticks = max(1, min(1000, $ticks));

        $this->sagaService->runBatch($saga, $ticks);

        return response()->json([
            'success' => true,
            'message' => "Advanced {$ticks} tick(s).",
            'ticks'   => $ticks,
        ]);
    }

    /**
     * POST /api/writer/saga/{sagaId}/run — start/resume simulation (legacy job).
     */
    public function run(string $sagaId): JsonResponse
    {
        $saga = Saga::findOrFail($sagaId);
        if ($saga->isComplete()) {
            return response()->json(['error' => 'Saga already completed.'], 422);
        }

        RunSagaSimulationJob::dispatch($saga);

        return response()->json([
            'success' => true,
            'message' => 'Simulation started in background.',
        ]);
    }
}
