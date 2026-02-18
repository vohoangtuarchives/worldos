<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Repositories;

use App\Domains\Cosmology\Contracts\AttractorInfluenceSnapshotRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AttractorInfluenceSnapshotRepository implements AttractorInfluenceSnapshotRepositoryInterface
{
    public function save(
        string $universeId,
        int $tick,
        string $dominantAttractor,
        array $influences,
        int $consecutiveCycles = 0
    ): void {
        DB::table('attractor_influence_snapshots')->updateOrInsert(
            [
                'universe_id' => $universeId,
                'tick' => $tick,
            ],
            [
                'dominant_attractor' => $dominantAttractor,
                'influences_jsonb' => json_encode($influences),
                'consecutive_cycles' => $consecutiveCycles,
            ]
        );
    }

    /**
     * @return array{dominant_attractor: string, influences_jsonb: array, consecutive_cycles: int}|null
     */
    public function getLatestBefore(string $universeId, int $tick): ?array
    {
        $row = DB::table('attractor_influence_snapshots')
            ->where('universe_id', $universeId)
            ->where('tick', '<', $tick)
            ->orderByDesc('tick')
            ->first();

        if ($row === null) {
            return null;
        }

        return [
            'dominant_attractor' => $row->dominant_attractor,
            'influences_jsonb' => is_string($row->influences_jsonb) ? json_decode($row->influences_jsonb, true) : $row->influences_jsonb,
            'consecutive_cycles' => (int) $row->consecutive_cycles,
        ];
    }

    /**
     * @return list<array{tick: int, dominant_attractor: string, influences_jsonb: array}>
     */
    public function getRange(string $universeId, int $fromTick, int $toTick): array
    {
        $rows = DB::table('attractor_influence_snapshots')
            ->where('universe_id', $universeId)
            ->where('tick', '>=', $fromTick)
            ->where('tick', '<=', $toTick)
            ->orderBy('tick')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'tick' => (int) $row->tick,
                'dominant_attractor' => $row->dominant_attractor,
                'influences_jsonb' => is_string($row->influences_jsonb) ? json_decode($row->influences_jsonb, true) : $row->influences_jsonb,
            ];
        }
        return $out;
    }
}
