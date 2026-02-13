<?php

declare(strict_types=1);

namespace App\Domains\Cosmic\Contracts;

use App\Domains\Cosmic\ValueObjects\WorldSnapshot;

/**
 * Repository contract for persisting cosmic simulation snapshots.
 */
interface CosmicSnapshotRepositoryInterface
{
    /**
     * Store a new cosmic snapshot.
     */
    public function saveSnapshot(int $worldId, WorldSnapshot $snapshot): void;

    /**
     * Store a cosmic event.
     */
    public function saveEvent(int $worldId, array $event): void;

    /**
     * Load the latest snapshot for a world.
     */
    public function latestSnapshot(int $worldId): ?WorldSnapshot;

    /**
     * Load a snapshot at a specific year.
     */
    public function snapshotAt(int $worldId, int $year): ?WorldSnapshot;

    /**
     * Get all snapshots for a world (timeline).
     */
    public function timeline(int $worldId, int $limit = 100): array;

    /**
     * Get all events for a world.
     */
    public function events(int $worldId): array;
}
