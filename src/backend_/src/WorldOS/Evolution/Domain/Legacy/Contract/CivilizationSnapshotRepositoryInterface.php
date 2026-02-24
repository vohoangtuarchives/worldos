<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Contract;

use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldStateVector;

interface CivilizationSnapshotRepositoryInterface
{
    public function saveSnapshot(
        string $universeId,
        int $tick,
        ?string $stage,
        ?float $pressure,
        WorldStateVector $state
    ): void;

    /** @return array{universe_id: string, tick: int, stage: ?string, pressure: ?float, state_jsonb: array}|null */
    public function getSnapshot(string $universeId, int $tick): ?array;

    /** @return array{universe_id: string, tick: int, stage: ?string, pressure: ?float, state_jsonb: array}|null */
    public function getLatestSnapshotBefore(string $universeId, int $tick): ?array;

    public function saveDiff(string $universeId, int $fromTick, int $toTick, array $diff): void;

    /** @return list<array{from_tick: int, to_tick: int, diff_jsonb: array}> */
    public function getDiffs(string $universeId, int $fromTick, int $toTick): array;

    /**
     * Get snapshots in a tick range for trajectory analysis.
     *
     * @return list<array{universe_id: string, tick: int, stage: ?string, pressure: ?float, state_jsonb: array}>
     */
    public function getSnapshotsRange(string $universeId, int $fromTick, int $toTick, ?int $limit = null): array;
}


