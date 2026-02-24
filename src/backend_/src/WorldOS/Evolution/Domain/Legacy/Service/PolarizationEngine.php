<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\PopulationCluster;

final class PolarizationEngine
{
    /**
     * Calculates the Polarization Index based on Pairwise-Distance Weighted approach.
     * High polarization means the society is divided into distinct, opposing clusters.
     *
     * @param PopulationCluster[] $clusters
     * @return float
     */
    public function calculatePolarization(array $clusters): float
    {
        if (count($clusters) < 2) {
            return 0.0;
        }

        $polarization = 0.0;
        $totalShare = 0.0;

        // Ensure total share is 1.0, though it should be already
        foreach ($clusters as $cluster) {
            $totalShare += $cluster->share;
        }

        if ($totalShare <= 0) {
            return 0.0;
        }

        $keys = array_keys($clusters);
        for ($i = 0; $i < count($keys); $i++) {
            for ($j = $i + 1; $j < count($keys); $j++) {
                $c1 = $clusters[$keys[$i]];
                $c2 = $clusters[$keys[$j]];
                
                $dist = $c1->ideology->distanceTo($c2->ideology);
                
                // Weight distance by the product of their normalized shares
                $w1 = $c1->share / $totalShare;
                $w2 = $c2->share / $totalShare;
                
                // Pairwise polarization is added twice (i to j, j to i) conceptually, 
                // but we multiply by suitable factor below if needed.
                $polarization += ($w1 * $w2 * $dist);
            }
        }

        // Multiply by 2.0 to account for the full matrix (since we only did i < j)
        $polarization *= 2.0;

        return $polarization;
    }
}
