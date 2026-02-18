<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Domains\Saga\Saga;
use App\Jobs\RunSagaSimulationJob;
use Illuminate\Http\JsonResponse;

/**
 * Writer API: Saga management (list, tree, run).
 */
class WriterSagaController extends Controller
{
    /**
     * GET /api/writer/sagas — list all sagas with world counts.
     */
    public function index(): JsonResponse
    {
        $sagas = Saga::withCount('sagaWorlds')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn(Saga $s) => [
                'id'                => (string) $s->id,
                'name'              => $s->name,
                'status'            => $s->status,
                'saga_worlds_count' => $s->saga_worlds_count,
                'world_count'       => $s->world_count,
                'created_at'        => $s->created_at?->toIso8601String(),
                'updated_at'        => $s->updated_at?->toIso8601String(),
            ]);

        return response()->json($sagas);
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
     * GET /api/writer/saga/{sagaId}/tree — Yggdrasil tree data.
     */
    public function tree(string $sagaId): JsonResponse
    {
        $saga = Saga::findOrFail($sagaId);
        $nodes = $saga->sagaWorlds()->with('world')->get()->map(function ($sw) {
            $w = $sw->world;
            return [
                'id'                   => (string) ($w?->id ?? $sw->id),
                'parentId'             => $w?->parent_id !== null ? (string) $w->parent_id : null,
                'name'                 => $w?->name ?? ('World #' . $sw->sequence),
                'current_era'          => $w ? (string) floor(($w->current_time ?? 0) / 50) : '0',
                'status'               => $sw->status,
                'has_collapsed'        => $sw->hasCollapsed(),
                'sequence'             => $sw->sequence,
            ];
        });

        return response()->json(['nodes' => $nodes]);
    }

    /**
     * POST /api/writer/saga/{sagaId}/run — start/resume simulation.
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
