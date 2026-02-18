<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Models\UniverseModel;
use App\Models\UniverseSnapshot;
use Illuminate\Http\JsonResponse;

/**
 * Writer API: Universe-scoped resources (v3 — snapshot-first, metrics per universe).
 */
class WriterUniverseController extends Controller
{
    /**
     * GET /api/writer/universes/{universeId}/snapshots — list universe_snapshots by tick.
     */
    public function snapshots(string $universeId): JsonResponse
    {
        $universe = UniverseModel::find($universeId);
        if (!$universe) {
            return response()->json(['error' => 'Universe not found.'], 404);
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
        if (!$universe) {
            return response()->json(['error' => 'Universe not found.'], 404);
        }

        return response()->json([
            'tick' => (int) ($universe->age ?? 0),
            'state_vector' => $universe->state_vector ?? [],
            'entropy' => $universe->entropy !== null ? (float) $universe->entropy : null,
            'stability_index' => $universe->stability_index !== null ? (float) $universe->stability_index : null,
            'phase' => 'expansion',
        ]);
    }
}
