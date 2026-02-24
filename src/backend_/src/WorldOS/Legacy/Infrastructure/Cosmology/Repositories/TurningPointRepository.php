<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Infrastructure\Cosmology\Repositories;

use Illuminate\Support\Facades\DB;

class TurningPointRepository
{
    /**
     * @return list<array{id: int, universe_id: string, tick: int, type: string, payload: ?array}>
     */
    public function getByUniverse(string $universeId, ?int $fromTick = null, ?int $toTick = null): array
    {
        $query = DB::table('turning_points')->where('universe_id', $universeId)->orderBy('tick');
        if ($fromTick !== null) {
            $query->where('tick', '>=', $fromTick);
        }
        if ($toTick !== null) {
            $query->where('tick', '<=', $toTick);
        }
        $rows = $query->get();
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'id' => (int) $row->id,
                'universe_id' => $row->universe_id,
                'tick' => (int) $row->tick,
                'type' => $row->type,
                'payload' => is_string($row->payload) ? json_decode($row->payload, true) : $row->payload,
            ];
        }
        return $out;
    }

    public function insert(string $universeId, int $tick, string $type, ?array $payload = null): int
    {
        return (int) DB::table('turning_points')->insertGetId([
            'universe_id' => $universeId,
            'tick' => $tick,
            'type' => $type,
            'payload' => $payload !== null ? json_encode($payload) : null,
        ]);
    }
}
