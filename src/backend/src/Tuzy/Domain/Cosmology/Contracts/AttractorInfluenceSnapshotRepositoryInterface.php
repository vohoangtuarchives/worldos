<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmology\Contracts;

/**
 * Persist and read attractor influence per tick for meta-history.
 */
interface AttractorInfluenceSnapshotRepositoryInterface
{
    public function save(
        string $universeId,
        int $tick,
        string $dominantAttractor,
        array $influences,
        int $consecutiveCycles = 0
    ): void;

    /**
     * @return array{dominant_attractor: string, influences_jsonb: array, consecutive_cycles: int}|null
     */
    public function getLatestBefore(string $universeId, int $tick): ?array;

    /**
     * @return list<array{tick: int, dominant_attractor: string, influences_jsonb: array}>
     */
    public function getRange(string $universeId, int $fromTick, int $toTick): array;
}
