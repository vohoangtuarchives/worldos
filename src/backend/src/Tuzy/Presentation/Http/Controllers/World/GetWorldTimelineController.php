<?php

namespace Tuzy\Presentation\Http\Controllers\World;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\WorldChronicleEvent;
use Illuminate\Routing\Controller;

class GetWorldTimelineController extends Controller
{
    /**
     * Fetch timeline events for a given world, sorted by year ascending.
     */
    public function __invoke(string $worldId, Request $request): JsonResponse
    {
        $limit = $request->query('limit', 100);
        $severity = $request->query('severity');

        $query = WorldChronicleEvent::where('world_id', $worldId)
            ->orderBy('year', 'asc')
            ->orderBy('created_at', 'asc');

        if ($severity) {
            $query->where('severity', strtoupper($severity));
        }

        $events = $query->paginate((int) $limit);

        return response()->json([
            'status' => 'success',
            'data' => $events->items(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'total' => $events->total(),
            ]
        ]);
    }
}
