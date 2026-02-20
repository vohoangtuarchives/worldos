<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Models\UniverseModel;
use App\Models\UniverseSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tuzy\Domain\Runtime\Exception\UniverseNotFoundException;

/**
 * Writer API: Universe-scoped resources (v3 — snapshot-first, metrics per universe).
 */
class WriterUniverseController extends Controller
{
    public function __construct(
        private \App\Domains\Saga\Actions\ForkUniverseFromTickAction $forkAction,
        private \App\Domains\Saga\Actions\EvaluateUniverseAction $evaluateAction,
        private \App\Domains\Saga\Actions\ApplySelectionPressureAction $pressureAction
    ) {}

    /**
     * GET /api/writer/universes — list all universes (for selection).
     */
    public function index(): JsonResponse
    {
        $universes = UniverseModel::orderByDesc('created_at')->get(['id', 'name', 'created_at', 'status', 'world_id']);
        return response()->json($universes);
    }

    /**
     * GET /api/writer/universes/{universeId}/snapshots — list universe_snapshots by tick.
     */
    public function snapshots(string $universeId): JsonResponse
    {
        $universe = UniverseModel::find($universeId);
        if (! $universe) {
            throw UniverseNotFoundException::withId($universeId);
        }

        $snapshots = UniverseSnapshot::where('universe_id', $universeId)
            ->orderBy('tick')
            ->get()
            ->map(fn ($s) => [
                'id' => (string) $s->id,
                'universe_id' => (string) $s->universe_id,
                'tick' => (int) $s->tick,
                'state_vector' => $s->state_vector,
                'entropy' => $s->entropy !== null ? (float) $s->entropy : null,
                'stability_index' => $s->stability_index !== null ? (float) $s->stability_index : null,
                'metrics' => $s->metrics,
                'created_at' => $s->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data' => ['snapshots' => $snapshots->values()->all()],
        ]);
    }

    /**
     * GET /api/writer/universes/{universeId}/metrics — tick (age), state_vector, entropy, stability for Evolution view.
     */
    public function metrics(string $universeId): JsonResponse
    {
        $universe = UniverseModel::find($universeId);
        if (! $universe) {
            throw UniverseNotFoundException::withId($universeId);
        }

        return response()->json([
            'tick' => (int) ($universe->age ?? 0),
            'state_vector' => $universe->state_vector ?? [],
            'entropy' => $universe->entropy !== null ? (float) $universe->entropy : null,
            'stability_index' => $universe->stability_index !== null ? (float) $universe->stability_index : null,
            'phase' => 'expansion',
        ]);
    }

    /**
     * POST /api/writer/universes/{universeId}/fork — fork starting from a specific tick.
     */
    public function fork(Request $request, string $universeId): JsonResponse
    {
        $tick = (int) $request->input('tick');
        $sagaId = $request->input('saga_id');
        
        try {
            $newUniverse = $this->forkAction->execute($universeId, $tick, $sagaId);
            return response()->json([
                'success' => true,
                'message' => "Universe forked from tick {$tick}.",
                'data' => [
                    'id' => $newUniverse->id,
                    'name' => $newUniverse->name,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/writer/universes/{universeId}/evaluate — AI potential evaluation.
     */
    public function evaluate(string $universeId): JsonResponse
    {
        try {
            $result = $this->evaluateAction->execute($universeId);
            return response()->json([
                'success' => true,
                'data' => [
                    'recommendation' => $result->recommendation,
                    'ip_score' => $result->ipScore,
                    'suggestion' => $result->mutationSuggestion ? [
                        'type' => $result->mutationSuggestion->type,
                        'intensity' => $result->mutationSuggestion->intensity,
                    ] : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/writer/universes/{universeId}/pressure — manual kernel intervention.
     */
    public function applyPressure(Request $request, string $universeId): JsonResponse
    {
        $type = $request->input('type');
        $intensity = (float) $request->input('intensity', 0.5);

        try {
            $this->pressureAction->execute($universeId, $type, $intensity);
            return response()->json([
                'success' => true,
                'message' => "Applied {$type} pressure with intensity {$intensity}."
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * GET /api/writer/universes/{universeId}/style — get the active style of the world.
     */
    public function style(string $universeId): JsonResponse
    {
        $universe = UniverseModel::find($universeId);
        if (!$universe) {
            return response()->json(['error' => 'Universe not found.'], 404);
        }

        $style = \App\Models\UniverseStyle::where('world_id', $universe->world_id)
            ->where('is_active', true)
            ->first();

        return response()->json([
            'success' => true,
            'data' => $style
        ]);
    }
}
