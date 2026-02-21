<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Cosmology\Repositories;

use Tuzy\Domain\Cosmology\Contracts\AttractorRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AttractorRepository implements AttractorRepositoryInterface
{
    /**
     * @return array{id: int, universe_id: string, name: string, centroid_jsonb: array, origin_centroid_jsonb: array, birth_tick: int, mutation_count: int, active: bool}|null
     */
    public function getByUniverseAndName(string $universeId, string $name): ?array
    {
        $row = DB::table('universe_attractors')
            ->where('universe_id', $universeId)
            ->where('name', $name)
            ->where('active', true)
            ->first();

        if ($row === null) {
            return null;
        }
        return $this->rowToArray($row);
    }

    /**
     * @return list<array{id: int, universe_id: string, name: string, centroid_jsonb: array, origin_centroid_jsonb: array, birth_tick: int, mutation_count: int, active: bool}>
     */
    public function getActiveByUniverse(string $universeId): array
    {
        $rows = DB::table('universe_attractors')
            ->where('universe_id', $universeId)
            ->where('active', true)
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[] = $this->rowToArray($row);
        }
        return $out;
    }

    public function upsert(
        string $universeId,
        string $name,
        array $centroid,
        array $originCentroid,
        int $birthTick = 0,
        int $mutationCount = 0
    ): int {
        $existing = DB::table('universe_attractors')
            ->where('universe_id', $universeId)
            ->where('name', $name)
            ->first();

        $payload = [
            'centroid_jsonb' => json_encode($centroid),
            'origin_centroid_jsonb' => json_encode($originCentroid),
            'birth_tick' => $birthTick,
            'mutation_count' => $mutationCount,
            'active' => true,
        ];

        if ($existing !== null) {
            DB::table('universe_attractors')->where('id', $existing->id)->update($payload);
            return (int) $existing->id;
        }

        $id = DB::table('universe_attractors')->insertGetId([
            'universe_id' => $universeId,
            'name' => $name,
            'centroid_jsonb' => json_encode($centroid),
            'origin_centroid_jsonb' => json_encode($originCentroid),
            'birth_tick' => $birthTick,
            'mutation_count' => $mutationCount,
            'active' => true,
        ]);
        return $id;
    }

    public function recordCentroidHistory(int $attractorId, int $tick, array $centroid): void
    {
        DB::table('attractor_centroid_history')->insert([
            'attractor_id' => $attractorId,
            'tick' => $tick,
            'centroid_jsonb' => json_encode($centroid),
        ]);
    }

    public function incrementMutationCount(int $attractorId): void
    {
        DB::table('universe_attractors')->where('id', $attractorId)->increment('mutation_count');
    }

    /**
     * @param object $row
     * @return array{id: int, universe_id: string, name: string, centroid_jsonb: array, origin_centroid_jsonb: array, birth_tick: int, mutation_count: int, active: bool}
     */
    private function rowToArray(object $row): array
    {
        return [
            'id' => (int) $row->id,
            'universe_id' => $row->universe_id,
            'name' => $row->name,
            'centroid_jsonb' => is_string($row->centroid_jsonb) ? json_decode($row->centroid_jsonb, true) : $row->centroid_jsonb,
            'origin_centroid_jsonb' => is_string($row->origin_centroid_jsonb) ? json_decode($row->origin_centroid_jsonb, true) : $row->origin_centroid_jsonb,
            'birth_tick' => (int) $row->birth_tick,
            'mutation_count' => (int) $row->mutation_count,
            'active' => (bool) $row->active,
        ];
    }
}
