<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Contracts;

use App\Domains\Cosmology\ValueObjects\WorldSnapshot;

/**
 * Repository contract for persisting cosmic simulation snapshots (world_id, year).
 *
 * @deprecated WorldOS v3: New evolution flow uses universe_snapshots (UniverseSnapshotRepository). This interface remains for legacy SagaRunner path.
 */
interface CosmicSnapshotRepositoryInterface
{
    /**
     * Store a new cosmic snapshot.
     */
    public function saveSnapshot(string $worldId, WorldSnapshot $snapshot): void;

    /**
     * Store a cosmic event.
     */
    public function saveEvent(string $worldId, array $event): void;

    /**
     * Load the latest snapshot for a world.
     */
    public function latestSnapshot(string $worldId): ?WorldSnapshot;

    /**
     * Load a snapshot at a specific year.
     */
    public function snapshotAt(string $worldId, int $year): ?WorldSnapshot;

    /**
     * Get all snapshots for a world (timeline).
     */
    public function timeline(string $worldId, int $limit = 100): array;

    /**
     * Get all events for a world.
     */
    public function events(string $worldId): array;
}
