<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Contracts;

/**
 * Per-universe attractors (centroid + origin) for drift and mutation.
 */
interface AttractorRepositoryInterface
{
    /**
     * @return array{id: int, universe_id: string, name: string, centroid_jsonb: array, origin_centroid_jsonb: array, birth_tick: int, mutation_count: int, active: bool}|null
     */
    public function getByUniverseAndName(string $universeId, string $name): ?array;

    /**
     * @return list<array{id: int, universe_id: string, name: string, centroid_jsonb: array, origin_centroid_jsonb: array, birth_tick: int, mutation_count: int, active: bool}>
     */
    public function getActiveByUniverse(string $universeId): array;

    /**
     * Create or update attractor. Returns id.
     */
    public function upsert(
        string $universeId,
        string $name,
        array $centroid,
        array $originCentroid,
        int $birthTick = 0,
        int $mutationCount = 0
    ): int;

    public function recordCentroidHistory(int $attractorId, int $tick, array $centroid): void;

    public function incrementMutationCount(int $attractorId): void;
}
