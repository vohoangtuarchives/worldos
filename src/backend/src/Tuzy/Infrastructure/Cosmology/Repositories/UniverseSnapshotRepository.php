<?php

namespace Tuzy\Infrastructure\Cosmology\Repositories;

use Tuzy\Application\Cosmology\Entities\Universe;
use App\Models\UniverseSnapshot;

/**
 * WorldOS v3: Snapshot-first. Persist universe state at each tick for rollback, fork, clone, AI metrics.
 */
class UniverseSnapshotRepository
{
    /**
     * Save a snapshot at the universe's current tick. Called after each tick (or every N) from UniverseRuntimeService.
     *
     * @param array<string, mixed> $metrics Optional: complexity_index, narrative_score, stability_index, etc.
     */
    public function save(Universe $universe, array $metrics = []): void
    {
        $state = $universe->getState();
        $stateVector = $state->getAll();
        $entropy = $state->getEntropy();
        $stabilityIndex = $metrics['stability_index'] ?? null;
        $metricsPayload = $metrics;
        unset($metricsPayload['stability_index']);

        UniverseSnapshot::updateOrCreate(
            [
                'universe_id' => $universe->getId(),
                'tick' => $universe->getAge(),
            ],
            [
                'state_vector' => $stateVector,
                'entropy' => $entropy,
                'stability_index' => $stabilityIndex,
                'metrics' => $metricsPayload ?: null,
            ]
        );
    }

    /**
     * Get snapshot at a specific tick for a universe (for rollback/fork).
     */
    public function getAtTick(string $universeId, int $tick): ?UniverseSnapshot
    {
        return UniverseSnapshot::where('universe_id', $universeId)
            ->where('tick', $tick)
            ->first();
    }

    /**
     * Get the latest snapshot for a universe (by tick).
     */
    public function getLatest(string $universeId): ?UniverseSnapshot
    {
        return UniverseSnapshot::where('universe_id', $universeId)
            ->orderByDesc('tick')
            ->first();
    }
}
