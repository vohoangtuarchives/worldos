<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Mutation\Service;

use WorldOS\Evolution\Domain\Seed\ValueObject\UniverseSeed;
use WorldOS\Saga\Domain\Myth\ValueObject\MythVector;

final class SeedMutationEngine
{
    public function mutate(UniverseSeed $seed): UniverseSeed
    {
        $intensity = $this->adaptiveIntensity($seed);
        
        $newCoupling = $this->perturbMatrix($seed->couplingMatrix, $intensity);
        $newSpectralRadius = $this->mutateSpectralRadius($seed->spectralRadius);

        return new UniverseSeed(
            mythImprint: $this->mutateMyth($seed),
            couplingMatrix: $newCoupling,
            spectralRadius: $newSpectralRadius,
            entropyResidual: $seed->entropyResidual * 0.9, // Dampen residual entropy
            generation: $seed->generation + 1,
            parentUniverseId: null
        );
    }

    private function adaptiveIntensity(UniverseSeed $seed): float
    {
        // Base mutation intensity
        return 0.05; 
    }

    private function perturbMatrix(array $matrix, float $intensity): array
    {
        $newMatrix = [];
        foreach ($matrix as $key => $row) {
            foreach ($row as $colKey => $val) {
                // Add tiny Gaussian noise roughly approximated by uniform
                $noise = ((mt_rand() / mt_getrandmax()) * 2 - 1) * $intensity;
                $newMatrix[$key][$colKey] = $val + $noise;
            }
        }
        return $newMatrix;
    }

    private function mutateSpectralRadius(float $current): float
    {
        $noise = ((mt_rand() / mt_getrandmax()) * 2 - 1) * 0.01;
        // Strict guard-rail bounding
        return max(0.9, min(0.999, $current + $noise));
    }

    private function mutateMyth(UniverseSeed $seed): MythVector
    {
        $current = $seed->mythImprint->toArray();
        $genesis = MythVector::genesis()->toArray();
        
        $alpha = 0.5; // Mix inherited myth field

        $new = [];
        foreach (MythVector::DIMENSIONS as $dim) {
            $new[$dim] = ($alpha * $current[$dim]) + ((1 - $alpha) * $genesis[$dim]);
        }

        return MythVector::create($new);
    }
}
