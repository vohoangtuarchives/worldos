<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Infrastructure\Cosmology\Repositories;

use Illuminate\Support\Facades\DB;

class EpochRepository
{
    /**
     * @return list<array{id: int, universe_id: string, start_tick: int, end_tick: int, dominant_attractor: string, label: ?string}>
     */
    public function getByUniverse(string $universeId, ?int $fromTick = null, ?int $toTick = null): array
    {
        $query = DB::table('epochs')->where('universe_id', $universeId)->orderBy('start_tick');
        if ($fromTick !== null) {
            $query->where('end_tick', '>=', $fromTick);
        }
        if ($toTick !== null) {
            $query->where('start_tick', '<=', $toTick);
        }
        $rows = $query->get();
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'id' => (int) $row->id,
                'universe_id' => $row->universe_id,
                'start_tick' => (int) $row->start_tick,
                'end_tick' => (int) $row->end_tick,
                'dominant_attractor' => $row->dominant_attractor,
                'label' => $row->label,
            ];
        }
        return $out;
    }

    public function insert(string $universeId, int $startTick, int $endTick, string $dominantAttractor, ?string $label = null): int
    {
        return (int) DB::table('epochs')->insertGetId([
            'universe_id' => $universeId,
            'start_tick' => $startTick,
            'end_tick' => $endTick,
            'dominant_attractor' => $dominantAttractor,
            'label' => $label,
        ]);
    }
}
