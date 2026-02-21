<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Services;

use WorldOS\Domains\Evolution\Repositories\TurningPointRepository;

/**
 * Records turning points: dominant_shift and mutation.
 */
class TurningPointEngine
{
    public const TYPE_DOMINANT_SHIFT = 'dominant_shift';
    public const TYPE_MUTATION = 'mutation';

    public function __construct(
        private readonly TurningPointRepository $repository
    ) {
    }

    public function recordDominantShift(string $universeId, int $tick, string $fromAttractor, string $toAttractor): int
    {
        return $this->repository->insert($universeId, $tick, self::TYPE_DOMINANT_SHIFT, [
            'from' => $fromAttractor,
            'to' => $toAttractor,
        ]);
    }

    public function recordMutation(string $universeId, int $tick, string $attractorName, array $payload = []): int
    {
        return $this->repository->insert($universeId, $tick, self::TYPE_MUTATION, array_merge(
            ['attractor' => $attractorName],
            $payload
        ));
    }

    /**
     * @return list<array{id: int, universe_id: string, tick: int, type: string, payload: ?array}>
     */
    public function getByUniverse(string $universeId, ?int $fromTick = null, ?int $toTick = null): array
    {
        return $this->repository->getByUniverse($universeId, $fromTick, $toTick);
    }
}



