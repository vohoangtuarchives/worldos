<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\Kernel;

use App\Domains\Cosmic\Contracts\CosmicSnapshotRepositoryInterface;
use Tuzy\Domain\Cosmology\ValueObject\WorldSnapshot;
use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Infrastructure\Cosmology\Repositories\CosmologyRepository;
use Tuzy\Application\Evolution\Adapter\SnapshotToVectorAdapter;
use Tuzy\Application\Evolution\Adapter\VectorToSnapshotAdapter;
use App\Models\World;

/**
 * StateLoader - Load/save WorldStateVector at boundary using snapshot repository and adapters.
 * WorldOS 2.0: When resolving "current" year for load/save, prefers Universe runtime (getRuntimeStateForWorld) when available.
 */
final class StateLoader
{
    public function __construct(
        private readonly CosmicSnapshotRepositoryInterface $snapshotRepo,
        private readonly CosmologyRepository $cosmologyRepository,
        private readonly SnapshotToVectorAdapter $toVector,
        private readonly VectorToSnapshotAdapter $toSnapshot
    ) {
    }

    public function loadVector(World $world): WorldStateVector
    {
        $snapshot = $this->snapshotRepo->latestSnapshot((int) $world->id);
        if ($snapshot === null) {
            $year = $this->currentYearForWorld($world);
            return $this->defaultVector($year);
        }
        return $this->toVector->toVector($snapshot);
    }

    public function saveVector(World $world, WorldStateVector $vector): void
    {
        $prev = $this->snapshotRepo->latestSnapshot((int) $world->id);
        $year = $this->currentYearForWorld($world);
        $snapshot = $this->toSnapshot->toSnapshot($vector, $year, $prev);
        $this->snapshotRepo->saveSnapshot((int) $world->id, $snapshot);
    }

    private function currentYearForWorld(World $world): int
    {
        $runtime = $this->cosmologyRepository->getRuntimeStateForWorld((int) $world->id);
        return $runtime !== null ? $runtime['age'] : (int) ($world->current_time ?? 0);
    }

    public function loadSnapshot(World $world): ?WorldSnapshot
    {
        return $this->snapshotRepo->latestSnapshot((int) $world->id);
    }

    private function defaultVector(int $year): WorldStateVector
    {
        $s = WorldSnapshot::defaultObservation($year);
        return $this->toVector->toVector($s);
    }
}
