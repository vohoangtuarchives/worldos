<?php

namespace App\Http\Controllers\Api\Reader;

use App\Http\Controllers\Controller;
use App\Models\World;
use App\Domains\Reader\ReaderInteractionEngine;
use Tuzy\Domain\World\Exception\WorldNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * GET /api/reader/worlds/{id}/interact — payload for Reader interact page (epoch, narrative, choices).
 */
class ReaderInteractController extends Controller
{
    public function __construct(
        private ReaderInteractionEngine $engine
    ) {}

    public function interact(string $id): JsonResponse
    {
        $worldId = (int) $id;
        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId((string) $worldId);
        }

        $currentEpoch = (int) (DB::table('world_state_events')->where('world_id', $worldId)->max('epoch') ?? $world->current_tick ?? 0);

        $rawNarrative = DB::table('chronicles')
            ->where('world_id', $worldId)
            ->orderBy('epoch', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($row) => ['epoch' => $row->epoch, 'content' => $row->content ?? ''])
            ->values()
            ->all();

        $choices = [];
        try {
            $choices = $this->engine->getChoices($worldId, $currentEpoch);
        } catch (\Throwable $e) {
            // choices may fail if state not ready
        }

        return response()->json([
            'world' => [
                'id' => $world->id,
                'name' => $world->name ?? 'World ' . $world->id,
            ],
            'epoch' => $currentEpoch,
            'narrative' => array_reverse($rawNarrative),
            'choices' => $choices,
        ]);
    }
}
