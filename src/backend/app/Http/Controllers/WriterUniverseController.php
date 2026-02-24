<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\WorldOS\Runtime\Contracts\UniverseRepositoryInterface;
use App\WorldOS\Runtime\Contracts\UniverseSnapshotRepositoryInterface;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use Illuminate\Http\JsonResponse;

/**
 * Writer Universe Controller — Universe observation endpoints.
 *
 * From docs §13.1: GET /api/writer/universe/{id}/snapshot
 *
 * Read-only endpoints for observing Universe state.
 */
class WriterUniverseController extends Controller
{
    /**
     * GET /api/writer/universe/{id}/snapshot
     *
     * Get the latest snapshot of a Universe.
     */
    public function snapshot(
        string $id,
        UniverseRepositoryInterface $universeRepository,
        UniverseSnapshotRepositoryInterface $snapshotRepository,
    ): JsonResponse {
        $universeId = new UniverseId($id);
        $universe = $universeRepository->findById($universeId);

        if (!$universe) {
            return response()->json([
                'error' => "Universe not found: {$id}",
            ], 404);
        }

        $snapshot = $snapshotRepository->getLatest($universeId);

        return response()->json([
            'universe_id' => $id,
            'status' => $universe->getStatus()->value,
            'current_tick' => $universe->getCurrentTick(),
            'state_vector' => $universe->getStateVector()->toArray(),
            'snapshot' => $snapshot ? [
                'tick' => $snapshot->tick,
                'entropy' => $snapshot->stateVector->entropy,
                'order' => $snapshot->stateVector->order,
                'cohesion' => $snapshot->stateVector->cohesion,
                'innovation' => $snapshot->stateVector->innovation,
                'recorded_at' => $snapshot->recordedAt->format('Y-m-d H:i:s'),
            ] : null,
        ]);
    }
}
