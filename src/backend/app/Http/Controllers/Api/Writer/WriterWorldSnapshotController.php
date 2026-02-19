<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Models\World;
use App\Models\UniverseModel;
use App\Models\UniverseSnapshot;
use App\Models\WorldEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Writer API: World snapshots (V3: reads from universe_snapshots) and events.
 *
 * WorldOS V3: Snapshots are universe-level (universe_snapshots table).
 * This controller bridges world_id → universe_ids → snapshots for backward compatibility.
 */
class WriterWorldSnapshotController extends Controller
{
    /**
     * GET /api/writer/worlds/{id}/snapshots
     * List universe snapshots for all universes belonging to this world.
     * V3: Reads from universe_snapshots instead of cosmic_snapshots.
     */
    public function index(string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }

        $universeIds = UniverseModel::where('world_id', $world->id)
            ->pluck('id');

        $snapshots = UniverseSnapshot::whereIn('universe_id', $universeIds)
            ->orderBy('tick')
            ->limit(200)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'universe_id' => $s->universe_id,
                'tick' => (int) $s->tick,
                'entropy' => $s->entropy,
                'stability_index' => $s->stability_index,
                'state_vector' => $s->state_vector,
                'metrics' => $s->metrics,
                'created_at' => $s->created_at?->format(\DateTimeInterface::ATOM),
            ]);

        return response()->json([
            'success' => true,
            'data' => ['snapshots' => $snapshots->values()->all()],
        ]);
    }

    /**
     * GET /api/writer/worlds/{id}/snapshots/compare?tick_a=1&tick_b=2&universe_id=...
     * Compare two snapshots by tick for a specific universe.
     * V3: Uses universe_snapshots instead of cosmic_snapshots.
     */
    public function compare(Request $request, string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }

        $universeId = $request->query('universe_id');
        $tickA = (int) $request->query('tick_a', $request->query('year_a', 0));
        $tickB = (int) $request->query('tick_b', $request->query('year_b', 0));

        // If no universe_id provided, use the first active universe for this world
        if (!$universeId) {
            $universeId = UniverseModel::where('world_id', $world->id)
                ->where('is_archived', false)
                ->value('id');
        }

        if (!$universeId) {
            return response()->json(['error' => 'No active universe for this world'], 404);
        }

        $a = UniverseSnapshot::where('universe_id', $universeId)->where('tick', $tickA)->first();
        $b = UniverseSnapshot::where('universe_id', $universeId)->where('tick', $tickB)->first();

        $toPayload = fn (?UniverseSnapshot $s) => $s ? [
            'id' => $s->id,
            'universe_id' => $s->universe_id,
            'tick' => (int) $s->tick,
            'entropy' => $s->entropy,
            'stability_index' => $s->stability_index,
            'state_vector' => $s->state_vector,
            'metrics' => $s->metrics,
        ] : null;

        return response()->json([
            'success' => true,
            'data' => [
                'snapshot_a' => $toPayload($a),
                'snapshot_b' => $toPayload($b),
            ],
        ]);
    }

    /**
     * POST /api/writer/worlds/{id}/snapshots
     * Capture snapshot for the active universe of this world.
     */
    public function store(string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Snapshot capture requested (stub). Wire to pipeline to persist current state.',
        ], 202);
    }

    /**
     * GET /api/writer/worlds/{id}/events
     * Paginated world events (tick, type, payload).
     */
    public function events(Request $request, string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }

        $perPage = max(1, min(50, (int) $request->query('per_page', 20)));
        $type = $request->query('type');

        $query = WorldEvent::where('world_id', $world->id)->orderByDesc('tick');
        if ($type !== null && $type !== '') {
            $query->where('type', $type);
        }

        $paginator = $query->paginate($perPage);

        $items = $paginator->getCollection()->map(fn ($e) => [
            'id' => $e->id,
            'tick' => (int) $e->tick,
            'type' => $e->type,
            'payload' => $e->payload,
            'created_at' => $e->created_at?->format(\DateTimeInterface::ATOM),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'events' => $items->values()->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    /**
     * POST /api/writer/worlds/{id}/events/replay
     * Replay from tick. Stub until domain supports replay.
     */
    public function replay(Request $request, string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }

        $fromTick = (int) $request->input('from_tick', 0);

        return response()->json([
            'success' => true,
            'message' => "Replay from tick {$fromTick} requested (stub). Wire to domain when supported.",
        ], 202);
    }
}
