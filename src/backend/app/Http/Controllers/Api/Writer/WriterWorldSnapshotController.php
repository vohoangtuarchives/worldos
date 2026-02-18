<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Models\World;
use App\Models\CosmicSnapshot;
use App\Models\WorldEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Writer API: World snapshots (list, compare) and events (list, replay stub).
 */
class WriterWorldSnapshotController extends Controller
{
    /**
     * GET /api/writer/worlds/{id}/snapshots
     * List cosmic snapshots for world (ordered by year).
     */
    public function index(string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }

        $snapshots = CosmicSnapshot::where('world_id', $world->id)
            ->orderBy('year')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'world_id' => $s->world_id,
                'year' => (int) $s->year,
                'entropy' => $s->entropy,
                'stability' => $s->stability,
                'energy' => $s->energy,
                'created_at' => $s->created_at?->format(\DateTimeInterface::ATOM),
            ]);

        return response()->json([
            'success' => true,
            'data' => ['snapshots' => $snapshots->values()->all()],
        ]);
    }

    /**
     * GET /api/writer/worlds/{id}/snapshots/compare?year_a=1&year_b=2
     * Return two snapshots for diff.
     */
    public function compare(Request $request, string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }

        $yearA = (int) $request->query('year_a', 0);
        $yearB = (int) $request->query('year_b', 0);

        $a = CosmicSnapshot::where('world_id', $world->id)->where('year', $yearA)->first();
        $b = CosmicSnapshot::where('world_id', $world->id)->where('year', $yearB)->first();

        $toPayload = fn (?CosmicSnapshot $s) => $s ? [
            'id' => $s->id,
            'year' => (int) $s->year,
            'entropy' => $s->entropy,
            'stability' => $s->stability,
            'energy' => $s->energy,
            'tension' => $s->tension,
            'resonance' => $s->resonance,
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
     * Create/capture snapshot. Stub: returns message; wire to pipeline later to persist current state.
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
