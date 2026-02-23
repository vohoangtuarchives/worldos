<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\Services;

use WorldOS\Legacy\Domain\Cosmology\Contracts\AttractorRepositoryInterface;
use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Legacy\Application\Cosmology\Mathematics\AttractorField;

/**
 * Slow centroid drift when an attractor is dominant: new = old + alpha * (state - old).
 */
class CentroidDriftService
{
    public function __construct(
        private readonly AttractorRepositoryInterface $attractorRepository,
        private readonly AttractorField $attractorField
    ) {
    }

    public function driftIfDominant(string $universeId, int $tick, string $dominantAttractorName, WorldStateVector $state): void
    {
        $dims = WorldStateVector::dimensions();
        $stateArr = $state->getAll();

        $attractorRow = $this->attractorRepository->getByUniverseAndName($universeId, $dominantAttractorName);
        if ($attractorRow === null) {
            $default = $this->getDefaultCentroid($dominantAttractorName);
            if ($default === null) {
                return;
            }
            $this->attractorRepository->upsert($universeId, $dominantAttractorName, $default, $default, $tick, 0);
            $attractorRow = $this->attractorRepository->getByUniverseAndName($universeId, $dominantAttractorName);
            if ($attractorRow === null) {
                return;
            }
        }

        $attractorId = $attractorRow['id'];
        $currentCentroid = $attractorRow['centroid_jsonb'];
        $origin = $attractorRow['origin_centroid_jsonb'];
        $birthTick = $attractorRow['birth_tick'];
        $mutationCount = $attractorRow['mutation_count'];

        $alpha = (float) config('cosmology.attractor_drift_alpha', 0.02);
        $newCentroid = [];
        foreach ($dims as $dim) {
            $c = $currentCentroid[$dim] ?? 0.0;
            $s = $stateArr[$dim] ?? 0.0;
            $newCentroid[$dim] = max(0.0, min(1.0, $c + $alpha * ($s - $c)));
        }

        $this->attractorRepository->upsert($universeId, $dominantAttractorName, $newCentroid, $origin, $birthTick, $mutationCount);
        $this->attractorRepository->recordCentroidHistory($attractorId, $tick, $newCentroid);
    }

    private function getDefaultCentroid(string $name): ?array
    {
        foreach ($this->attractorField->getAttractors() as $a) {
            if ($a->getName() === $name) {
                return $a->getCentroid();
            }
        }
        return null;
    }
}
