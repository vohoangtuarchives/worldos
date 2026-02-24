<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\Contracts;

use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Runtime\ValueObjects\UniverseSnapshot;

/**
 * Universe Snapshot Repository Contract — Domain layer interface.
 */
interface UniverseSnapshotRepositoryInterface
{
    public function save(UniverseSnapshot $snapshot): void;

    /**
     * Get snapshot at a specific tick.
     */
    public function findByTick(UniverseId $universeId, int $tick): ?UniverseSnapshot;

    /**
     * Get the latest snapshot for a universe.
     */
    public function findLatest(UniverseId $universeId): ?UniverseSnapshot;

    /**
     * Get all snapshots for a universe, ordered by tick ascending.
     *
     * @return UniverseSnapshot[]
     */
    public function findAllByUniverse(UniverseId $universeId): array;

    /**
     * Get snapshots in a tick range.
     *
     * @return UniverseSnapshot[]
     */
    public function findByTickRange(UniverseId $universeId, int $fromTick, int $toTick): array;
}
