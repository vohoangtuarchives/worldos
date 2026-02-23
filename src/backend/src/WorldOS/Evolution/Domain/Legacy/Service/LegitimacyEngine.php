<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\PopulationCluster;
use WorldOS\Evolution\Domain\Legacy\ValueObject\Faction;

final class LegitimacyEngine
{
    private const MAX_IDEOLOGY_DISTANCE = 2.4494897; // sqrt(6) -> max possible distance between two IdeologyVectors

    /**
     * Calculates Emergent Legitimacy by computing how well the Ruling Faction 
     * represents the various Population Clusters (Support Mapping).
     *
     * @param PopulationCluster[] $clusters
     * @param Faction $rulingFaction
     * @return float (0.0 to 1.0)
     */
    public function calculateLegitimacy(array $clusters, Faction $rulingFaction): float
    {
        if (empty($clusters)) {
            return $rulingFaction->legitimacyClaim; // Fallback if no population data
        }

        $legitimacy = 0.0;
        $totalShare = 0.0;

        foreach ($clusters as $cluster) {
            $totalShare += $cluster->share;

            $dist = $cluster->ideology->distanceTo($rulingFaction->ideology);
            
            // Support is inversely proportional to ideological distance
            $baseSupport = max(0.0, 1.0 - ($dist / self::MAX_IDEOLOGY_DISTANCE));
            
            // Radicalization amplifies the mismatch penalty. 
            // Radicals are much less likely to tolerate a ruling faction that doesn't perfectly align.
            $mismatchPenalty = ($dist / self::MAX_IDEOLOGY_DISTANCE) * $cluster->radicalization;
            $effectiveSupport = max(0.0, $baseSupport - $mismatchPenalty);
            
            $legitimacy += $cluster->share * $effectiveSupport;
        }

        // Normalize if totalShare is not exactly 1.0
        if ($totalShare > 0) {
            $legitimacy /= $totalShare;
        }

        return min(1.0, max(0.0, $legitimacy));
    }

    /**
     * Calculate structural Mismatch (the gap between elite and population average).
     */
    public function calculateMismatch(array $clusters, Faction $rulingFaction): float
    {
        if (empty($clusters)) {
            return 0.0;
        }

        $mismatch = 0.0;
        $totalShare = 0.0;

        foreach ($clusters as $cluster) {
            $totalShare += $cluster->share;
            $dist = $cluster->ideology->distanceTo($rulingFaction->ideology);
            $mismatch += $cluster->share * $dist;
        }

        if ($totalShare > 0) {
            $mismatch /= $totalShare;
        }

        // Normalize it to [0, 1] interval approximately
        return min(1.0, $mismatch / self::MAX_IDEOLOGY_DISTANCE);
    }
}
