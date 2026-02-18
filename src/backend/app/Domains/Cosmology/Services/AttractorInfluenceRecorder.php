<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Services;

use App\Domains\Cosmology\Contracts\AttractorInfluenceSnapshotRepositoryInterface;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Mathematics\AttractorField;
use App\Domains\Cosmology\Mathematics\InertiaPolicy;

/**
 * After each tick: compute attractor influences, resolve dominant with inertia, persist, then drift centroid.
 * Records turning point when dominant changes.
 */
class AttractorInfluenceRecorder
{
    public function __construct(
        private readonly AttractorField $attractorField,
        private readonly AttractorInfluenceSnapshotRepositoryInterface $repository,
        private readonly InertiaPolicy $inertiaPolicy,
        private readonly ?CentroidDriftService $centroidDriftService = null,
        private readonly ?TurningPointEngine $turningPointEngine = null
    ) {
    }

    public function record(string $universeId, int $tick, WorldStateVector $state): void
    {
        $influences = $this->attractorField->influences($state);

        $previous = $this->repository->getLatestBefore($universeId, $tick);
        $currentDominant = $previous['dominant_attractor'] ?? null;
        $consecutive = $previous['consecutive_cycles'] ?? 0;

        $resolved = $this->inertiaPolicy->resolveDominant($currentDominant, $influences, $consecutive);

        $newDominant = $resolved['dominant'];
        if ($this->turningPointEngine !== null && $currentDominant !== null && $currentDominant !== '' && $newDominant !== '' && $newDominant !== $currentDominant) {
            $this->turningPointEngine->recordDominantShift($universeId, $tick, $currentDominant, $newDominant);
        }

        $this->repository->save(
            $universeId,
            $tick,
            $newDominant,
            $influences,
            $resolved['consecutive_cycles']
        );

        if ($this->centroidDriftService !== null && $newDominant !== '') {
            $this->centroidDriftService->driftIfDominant($universeId, $tick, $newDominant, $state);
        }
    }
}
