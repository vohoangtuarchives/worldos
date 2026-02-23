<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldStateVector;

/**
 * Detects when an attractor has drifted enough from origin to count as mutation
 * (identity change). Uses high thresholds for stable, slow mutation (option A).
 */
class MutationDetector
{
    public function __construct(
        private readonly float $distanceThreshold,
        private readonly int $minDominanceCycles,
        private readonly float $minPressurePeak
    ) {
    }

    public static function fromConfig(): self
    {
        return new self(
            (float) config('cosmology.mutation_distance_threshold', 0.35),
            (int) config('cosmology.mutation_min_dominance_cycles', 150),
            (float) config('cosmology.mutation_min_pressure_peak', 0.85)
        );
    }

    /**
     * @param array{centroid_jsonb: array, origin_centroid_jsonb: array} $attractorRow
     */
    public function shouldMutate(array $attractorRow, int $dominanceDurationCycles, float $maxPressureInWindow): bool
    {
        $centroid = $attractorRow['centroid_jsonb'];
        $origin = $attractorRow['origin_centroid_jsonb'];
        $distance = $this->distance($centroid, $origin);

        return $distance >= $this->distanceThreshold
            && $dominanceDurationCycles >= $this->minDominanceCycles
            && $maxPressureInWindow >= $this->minPressurePeak;
    }

    private function distance(array $a, array $b): float
    {
        $dims = array_unique(array_merge(array_keys($a), array_keys($b)));
        $sum = 0.0;
        foreach ($dims as $dim) {
            $x = $a[$dim] ?? 0.0;
            $y = $b[$dim] ?? 0.0;
            $sum += ($x - $y) ** 2;
        }
        return sqrt($sum);
    }
}



