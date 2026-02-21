<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Cosmology\Repositories;

use Tuzy\Domain\Cosmology\Contracts\CivilizationSnapshotRepositoryInterface;
use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Illuminate\Support\Facades\DB;

class CivilizationSnapshotRepository implements CivilizationSnapshotRepositoryInterface
{
    public function saveSnapshot(
        string $universeId,
        int $tick,
        ?string $stage,
        ?float $pressure,
        WorldStateVector $state
    ): void {
        $stateJson = $state->getAll();

        DB::table('civilization_snapshots')->updateOrInsert(
            [
                'universe_id' => $universeId,
                'tick' => $tick,
            ],
            [
                'stage' => $stage,
                'pressure' => $pressure,
                'state_jsonb' => json_encode($stateJson),
            ]
        );
    }

    public function getSnapshot(string $universeId, int $tick): ?array
    {
        $row = DB::table('civilization_snapshots')
            ->where('universe_id', $universeId)
            ->where('tick', $tick)
            ->first();

        if ($row === null) {
            return null;
        }

        return [
            'universe_id' => $row->universe_id,
            'tick' => (int) $row->tick,
            'stage' => $row->stage,
            'pressure' => $row->pressure !== null ? (float) $row->pressure : null,
            'state_jsonb' => is_string($row->state_jsonb) ? json_decode($row->state_jsonb, true) : $row->state_jsonb,
        ];
    }

    public function getLatestSnapshotBefore(string $universeId, int $tick): ?array
    {
        $row = DB::table('civilization_snapshots')
            ->where('universe_id', $universeId)
            ->where('tick', '<=', $tick)
            ->orderByDesc('tick')
            ->first();

        if ($row === null) {
            return null;
        }

        return [
            'universe_id' => $row->universe_id,
            'tick' => (int) $row->tick,
            'stage' => $row->stage,
            'pressure' => $row->pressure !== null ? (float) $row->pressure : null,
            'state_jsonb' => is_string($row->state_jsonb) ? json_decode($row->state_jsonb, true) : $row->state_jsonb,
        ];
    }

    public function saveDiff(string $universeId, int $fromTick, int $toTick, array $diff): void
    {
        DB::table('civilization_diffs')->updateOrInsert(
            [
                'universe_id' => $universeId,
                'from_tick' => $fromTick,
                'to_tick' => $toTick,
            ],
            [
                'diff_jsonb' => json_encode($diff),
            ]
        );
    }

    public function getDiffs(string $universeId, int $fromTick, int $toTick): array
    {
        $rows = DB::table('civilization_diffs')
            ->where('universe_id', $universeId)
            ->where('from_tick', '>=', $fromTick)
            ->where('to_tick', '<=', $toTick)
            ->orderBy('from_tick')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'from_tick' => (int) $row->from_tick,
                'to_tick' => (int) $row->to_tick,
                'diff_jsonb' => is_string($row->diff_jsonb) ? json_decode($row->diff_jsonb, true) : $row->diff_jsonb,
            ];
        }
        return $out;
    }

    /**
     * Get snapshots in a tick range for trajectory analysis.
     *
     * @return list<array{universe_id: string, tick: int, stage: ?string, pressure: ?float, state_jsonb: array}>
     */
    public function getSnapshotsRange(string $universeId, int $fromTick, int $toTick, ?int $limit = null): array
    {
        $query = DB::table('civilization_snapshots')
            ->where('universe_id', $universeId)
            ->where('tick', '>=', $fromTick)
            ->where('tick', '<=', $toTick)
            ->orderBy('tick');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $rows = $query->get();
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'universe_id' => $row->universe_id,
                'tick' => (int) $row->tick,
                'stage' => $row->stage,
                'pressure' => $row->pressure !== null ? (float) $row->pressure : null,
                'state_jsonb' => is_string($row->state_jsonb) ? json_decode($row->state_jsonb, true) : $row->state_jsonb,
            ];
        }
        return $out;
    }
}
