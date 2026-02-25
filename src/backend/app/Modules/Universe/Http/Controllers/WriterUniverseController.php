<?php

declare(strict_types=1);

namespace App\Modules\Universe\Http\Controllers;

use App\Modules\Universe\Contracts\UniverseRepositoryInterface;
use App\Modules\Universe\Contracts\UniverseSnapshotRepositoryInterface;
use App\Modules\Universe\ValueObjects\UniverseId;
use Illuminate\Http\JsonResponse;

/**
 * Writer Universe Controller — Universe observation endpoints.
 *
 * From docs §13.1: GET /api/writer/universe/{id}/snapshot
 *
 * Read-only endpoints for observing Universe state.
 */
class WriterUniverseController extends \App\Http\Controllers\Controller
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
            'cascade_state' => $universe->getCascadeState()?->toArray(),
            'snapshot' => $snapshot ? [
                'tick' => $snapshot->tick,
                'entropy' => $snapshot->stateVector->entropy,
                'order' => $snapshot->stateVector->order,
                'cohesion' => $snapshot->stateVector->cohesion,
                'innovation' => $snapshot->stateVector->innovation,
                'inequality' => $snapshot->stateVector->inequality,
                'trauma' => $snapshot->stateVector->trauma,
                'cascade_state' => $snapshot->cascadeState ? [
                    'physics' => $snapshot->cascadeState->physics,
                    'chemistry' => $snapshot->cascadeState->chemistry,
                    'biology' => $snapshot->cascadeState->biology,
                    'cognition' => $snapshot->cascadeState->cognition,
                    'culture' => $snapshot->cascadeState->culture,
                ] : null,
                'recorded_at' => $snapshot->recordedAt->format('Y-m-d H:i:s'),
            ] : null,
        ]);
    }
}
