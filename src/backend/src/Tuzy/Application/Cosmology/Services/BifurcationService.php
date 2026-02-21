<?php

namespace Tuzy\Application\Cosmology\Services;

use Tuzy\Domain\Cosmology\Contracts\CivilizationSnapshotRepositoryInterface;
use Tuzy\Application\Cosmology\Entities\Universe;
use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Infrastructure\Cosmology\Repositories\CosmologyRepository;
use App\Models\UniverseModel;
use Illuminate\Support\Str;

class BifurcationService
{
    public function __construct(
        private readonly CosmologyRepository $repository,
        private readonly ?CivilizationSnapshotRepositoryInterface $snapshotRepo = null
    ) {
    }

    /**
     * Splits a universe into two parallel timelines.
     */
    public function split(Universe $u): array
    {
        $branch1 = $this->createBranch($u, "Prime");
        $branch2 = $this->createBranch($u, "Echo");

        $sourceModel = UniverseModel::find($u->getId());
        $worldId = $sourceModel?->world_id;

        $this->repository->save($branch1, $worldId);
        $this->repository->save($branch2, $worldId);

        return [$branch1, $branch2];
    }

    private function createBranch(Universe $parent, string $suffix): Universe
    {
        $s = $parent->getState();
        $params = $parent->getParameters();
        $seedBase = (string) ($params['random_seed'] ?? $parent->getId());
        $branchSeed = $this->deterministicBranchSeed($seedBase, $parent->getAge(), $suffix);

        // Deterministic noise per dimension: +/- 5%
        $vals = [
            $s->getOrder(),
            $s->getEntropy(),
            $s->getCohesion(),
            $s->getLegitimacy(),
            $s->getInnovation(),
            $s->getMilitary(),
            $s->getInequality(),
            $s->getTrauma(),
            $s->getEliteCohesion(),
            $s->getResourceStock(),
        ];
        $noisy = [];
        foreach ($vals as $i => $val) {
            $change = $this->deterministicNoise($branchSeed, $i);
            $noisy[] = max(0.0, min(1.0, $val + $change));
        }

        $newState = WorldStateVector::create(
            $noisy[0], $noisy[1], $noisy[2], $noisy[3], $noisy[4],
            $noisy[5], $noisy[6], $noisy[7], $noisy[8], $noisy[9]
        );

        $params['ancestors'] = [$parent->getId()];
        $params['event'] = 'BIFURCATION';
        $params['branch_type'] = $suffix;
        $params['random_seed'] = $branchSeed;

        return new Universe(
            $newState,
            $params,
            (string) Str::uuid(),
            $parent->getAge(),
            $parent->getCoords()
        );
    }

    /** Deterministic branch seed from parent seed + age + suffix (reproducible fork). */
    private function deterministicBranchSeed(string $parentSeed, int $age, string $suffix): int
    {
        $hash = md5($parentSeed . '|' . $age . '|' . $suffix);
        return (int) hexdec(substr($hash, 0, 8));
    }

    /** Deterministic noise in [-0.05, 0.05] for dimension index (no mt_rand). */
    private function deterministicNoise(int $seed, int $dimIndex): float
    {
        $h = crc32($seed . '|' . $dimIndex);
        $h = (int) (($h % 101) - 50); // -50..50
        return $h / 1000.0;
    }

    /**
     * Fork a new timeline from a snapshot at the given tick (deterministic).
     * Loads snapshot at largest tick <= atTick, replays diffs to atTick, clones state with noise, saves new universe.
     * Does not modify the original universe.
     */
    public function forkFromSnapshot(string $universeId, int $atTick, string $newSeedSuffix): ?Universe
    {
        $parent = $this->repository->find($universeId);
        if ($parent === null) {
            return null;
        }

        $stateAtTick = $this->reconstructStateAtTick($universeId, $atTick);
        if ($stateAtTick === null) {
            $stateAtTick = $parent->getState();
        }

        $params = $parent->getParameters();
        $seedBase = (string) ($params['random_seed'] ?? $parent->getId());
        $branchSeed = $this->deterministicBranchSeed($seedBase, $atTick, $newSeedSuffix);

        $vals = [
            $stateAtTick->getOrder(),
            $stateAtTick->getEntropy(),
            $stateAtTick->getCohesion(),
            $stateAtTick->getLegitimacy(),
            $stateAtTick->getInnovation(),
            $stateAtTick->getMilitary(),
            $stateAtTick->getInequality(),
            $stateAtTick->getTrauma(),
            $stateAtTick->getEliteCohesion(),
            $stateAtTick->getResourceStock(),
        ];
        $noisy = [];
        foreach ($vals as $i => $val) {
            $change = $this->deterministicNoise($branchSeed, $i);
            $noisy[] = max(0.0, min(1.0, $val + $change));
        }

        $newState = WorldStateVector::create(
            $noisy[0], $noisy[1], $noisy[2], $noisy[3], $noisy[4],
            $noisy[5], $noisy[6], $noisy[7], $noisy[8], $noisy[9]
        );

        $newParams = $params;
        $newParams['ancestors'] = array_merge($newParams['ancestors'] ?? [], [$universeId]);
        $newParams['event'] = 'FORK_FROM_SNAPSHOT';
        $newParams['fork_at_tick'] = $atTick;
        $newParams['random_seed'] = $branchSeed;

        $sourceModel = UniverseModel::find($universeId);
        $worldId = $sourceModel?->world_id;

        $newUniverse = new Universe(
            $newState,
            $newParams,
            (string) Str::uuid(),
            $atTick,
            $parent->getCoords(),
            $parent->getCosmicFactionId()
        );

        $this->repository->save($newUniverse, $worldId);
        return $newUniverse;
    }

    private function reconstructStateAtTick(string $universeId, int $atTick): ?WorldStateVector
    {
        if ($this->snapshotRepo === null) {
            return null;
        }

        $snap = $this->snapshotRepo->getLatestSnapshotBefore($universeId, $atTick);
        if ($snap === null) {
            return null;
        }

        $state = $snap['state_jsonb'];
        $baseTick = $snap['tick'];
        if ($baseTick === $atTick) {
            return WorldStateVector::fromArray($state);
        }

        $diffs = $this->snapshotRepo->getDiffs($universeId, $baseTick, $atTick);
        foreach ($diffs as $row) {
            $delta = $row['diff_jsonb'];
            foreach ($delta as $dim => $dv) {
                $state[$dim] = ($state[$dim] ?? 0) + (float) $dv;
                $state[$dim] = max(0.0, min(1.0, $state[$dim]));
            }
        }

        return WorldStateVector::fromArray($state);
    }
}
