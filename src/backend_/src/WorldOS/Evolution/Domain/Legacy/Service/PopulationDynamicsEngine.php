<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\PopulationCluster;
use WorldOS\Evolution\Domain\Legacy\ValueObject\Faction;
use WorldOS\Evolution\Domain\Legacy\ValueObject\IdeologyVector;

final class PopulationDynamicsEngine
{
    private const MAX_CLUSTERS = 6;
    private const MIN_SHARE_THRESHOLD = 0.03;
    private const MAX_DRIFT_PER_TICK = 0.02;

    /**
     * @param PopulationCluster[] $clusters
     * @param Faction[] $factions
     * @return PopulationCluster[]
     */
    public function evolve(
        array $clusters,
        array $factions,
        float $prosperity,
        float $resourceStress,
        float $inequality,
        float $chaosLevel,
        float $shockIntensity,
        int $currentTick
    ): array {
        if (empty($clusters)) {
            return [];
        }

        // 1. Material Pressure & Cultural Drift
        $clusters = $this->applyDrift(
            $clusters, 
            $factions, 
            $prosperity, 
            $resourceStress, 
            $inequality, 
            $chaosLevel, 
            $shockIntensity
        );

        // 2. Radicalization Update
        $clusters = $this->updateRadicalization($clusters, $resourceStress, $inequality);

        // 3. Dissolve insignificant clusters
        $clusters = $this->applyDissolve($clusters);

        // 4. Merge nearby clusters under pressure
        $clusters = $this->applyMerge($clusters, $shockIntensity);

        // 5. Split extreme radical clusters
        $clusters = $this->applySplit($clusters, $currentTick);

        return array_values($clusters);
    }

    private function applyDrift(
        array $clusters,
        array $factions,
        float $prosperity,
        float $resourceStress,
        float $inequality,
        float $chaosLevel,
        float $shockIntensity
    ): array {
        // Find dominant faction for influence
        $dominantFaction = null;
        $maxPower = -1.0;
        foreach ($factions as $faction) {
            if ($faction->powerShare > $maxPower) {
                $maxPower = $faction->powerShare;
                $dominantFaction = $faction;
            }
        }

        $result = [];
        foreach ($clusters as $cluster) {
            $idv = $cluster->ideology;

            // Material Pressures
            $dEconomic = -$resourceStress * 0.05 + $prosperity * 0.02;
            $dCentralization = $chaosLevel * 0.04 - $prosperity * 0.02;
            $dCulture = $prosperity * 0.03 - $inequality * 0.02;
            $dMilitary = $chaosLevel * 0.03;
            $dInnovation = $prosperity * 0.02 - $chaosLevel * 0.02;
            $dInstitution = -$chaosLevel * 0.05;

            // Shock Trauma
            if ($shockIntensity > 0.5) {
                $dCentralization += 0.1 * ($shockIntensity - 0.5);
                $dMilitary += 0.1 * ($shockIntensity - 0.5);
            }

            // Elite Influence (pull towards dominant faction)
            if ($dominantFaction) {
                $influenceRate = 0.01;
                // High radicalization resists elite influence
                $resistance = $cluster->radicalization;
                $effectiveInfluence = $influenceRate * (1.0 - $resistance);
                
                $domIdv = $dominantFaction->ideology;
                $dCentralization += ($domIdv->centralization - $idv->centralization) * $effectiveInfluence;
                $dEconomic       += ($domIdv->economic - $idv->economic) * $effectiveInfluence;
                $dCulture        += ($domIdv->culture - $idv->culture) * $effectiveInfluence;
                $dInnovation     += ($domIdv->innovation - $idv->innovation) * $effectiveInfluence;
                $dMilitary       += ($domIdv->military - $idv->military) * $effectiveInfluence;
                $dInstitution    += ($domIdv->institution - $idv->institution) * $effectiveInfluence;
            }

            // Clamp max drift per tick to avoid sudden non-historical jumps
            $dCen = max(-self::MAX_DRIFT_PER_TICK, min(self::MAX_DRIFT_PER_TICK, $dCentralization));
            $dEco = max(-self::MAX_DRIFT_PER_TICK, min(self::MAX_DRIFT_PER_TICK, $dEconomic));
            $dCul = max(-self::MAX_DRIFT_PER_TICK, min(self::MAX_DRIFT_PER_TICK, $dCulture));
            $dInn = max(-self::MAX_DRIFT_PER_TICK, min(self::MAX_DRIFT_PER_TICK, $dInnovation));
            $dMil = max(-self::MAX_DRIFT_PER_TICK, min(self::MAX_DRIFT_PER_TICK, $dMilitary));
            $dIns = max(-self::MAX_DRIFT_PER_TICK, min(self::MAX_DRIFT_PER_TICK, $dInstitution));

            $newIdeology = $idv->applyDrift($dCen, $dEco, $dCul, $dInn, $dMil, $dIns);
            
            // Maintain properties, radicalization updated in next step
            $result[] = $cluster->withDrift($newIdeology, 0.0);
        }

        return $result;
    }

    private function updateRadicalization(array $clusters, float $resourceStress, float $inequality): array
    {
        $result = [];
        foreach ($clusters as $cluster) {
            $stressFactor = ($resourceStress * 0.5 + $inequality * 0.5);
            // If stress is high, radicalization increases. If low, it decays.
            if ($stressFactor > 0.6) {
                $deltaRad = ($stressFactor - 0.6) * 0.05;
            } else {
                $deltaRad = -0.01; // natural decay
            }
            // Add a small drift wrapper
            $result[] = $cluster->withDrift($cluster->ideology, $deltaRad);
        }
        return $result;
    }

    private function applyDissolve(array $clusters): array
    {
        if (count($clusters) <= 1) return $clusters;

        $survivors = [];
        $dissolvedShares = [];

        foreach ($clusters as $i => $cluster) {
            if ($cluster->share < self::MIN_SHARE_THRESHOLD) {
                $dissolvedShares[] = $cluster;
            } else {
                $survivors[$i] = clone $cluster;
            }
        }

        if (empty($dissolvedShares) || empty($survivors)) {
            return $clusters;
        }

        // Redistribute dissolved shares to nearest surviving cluster
        foreach ($dissolvedShares as $deadCluster) {
            $minDist = PHP_FLOAT_MAX;
            $targetIndex = null;
            foreach ($survivors as $idx => $survivor) {
                $dist = $deadCluster->ideology->distanceTo($survivor->ideology);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $targetIndex = $idx;
                }
            }
            if ($targetIndex !== null) {
                $oldSurvivor = $survivors[$targetIndex];
                $survivors[$targetIndex] = $oldSurvivor->withShare($oldSurvivor->share + $deadCluster->share);
            }
        }

        return $survivors;
    }

    private function applyMerge(array $clusters, float $shockIntensity): array
    {
        if (count($clusters) <= 1) return $clusters;

        // Only merge if there's external pressure bringing people together
        if ($shockIntensity < 0.6) {
            return $clusters;
        }

        $minDist = PHP_FLOAT_MAX;
        $mergePair = null;

        $keys = array_keys($clusters);
        for ($i = 0; $i < count($keys); $i++) {
            for ($j = $i + 1; $j < count($keys); $j++) {
                $dist = $clusters[$keys[$i]]->ideology->distanceTo($clusters[$keys[$j]]->ideology);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $mergePair = [$keys[$i], $keys[$j]];
                }
            }
        }

        // Threshold to merge
        if ($mergePair !== null && $minDist < 0.4) {
            $c1 = $clusters[$mergePair[0]];
            $c2 = $clusters[$mergePair[1]];
            
            $totalShare = $c1->share + $c2->share;
            $w1 = $c1->share / $totalShare;
            $w2 = $c2->share / $totalShare;

            $newIdeology = new IdeologyVector(
                centralization: $c1->ideology->centralization * $w1 + $c2->ideology->centralization * $w2,
                economic: $c1->ideology->economic * $w1 + $c2->ideology->economic * $w2,
                culture: $c1->ideology->culture * $w1 + $c2->ideology->culture * $w2,
                innovation: $c1->ideology->innovation * $w1 + $c2->ideology->innovation * $w2,
                military: $c1->ideology->military * $w1 + $c2->ideology->military * $w2,
                institution: $c1->ideology->institution * $w1 + $c2->ideology->institution * $w2
            );

            $newRadicalization = $c1->radicalization * $w1 + $c2->radicalization * $w2;
            
            $mergedCluster = new PopulationCluster(
                ideology: $newIdeology,
                share: $totalShare,
                radicalization: $newRadicalization,
                originEventType: 'MERGE_SHOCK',
                birthTick: max($c1->birthTick, $c2->birthTick)
            );

            unset($clusters[$mergePair[0]]);
            unset($clusters[$mergePair[1]]);
            $clusters[] = $mergedCluster;
        }

        return $clusters;
    }

    private function applySplit(array $clusters, int $currentTick): array
    {
        if (count($clusters) >= self::MAX_CLUSTERS) {
            return $clusters;
        }

        $newClusters = [];
        $addedSplits = 0;

        foreach ($clusters as $i => $cluster) {
            // High radicalization and enough share causes a splinter group
            if ($cluster->radicalization > 0.8 && $cluster->share > 0.20 && $addedSplits === 0) {
                // Split 70/30
                $mainShare = $cluster->share * 0.7;
                $splinterShare = $cluster->share * 0.3;

                // Splinter group becomes even more extreme in a random direction or shifts away
                // For simplicity, we add deterministic small drift to simulate fracture
                $dCen = $cluster->ideology->centralization > 0.5 ? 0.2 : -0.2;
                $dEco = $cluster->ideology->economic > 0.5 ? -0.2 : 0.2; // opposite

                $splinterIdeology = clone $cluster->ideology;
                $splinterIdeology = $splinterIdeology->applyDrift($dCen, $dEco, 0.0, 0.0, 0.0, 0.0);

                $mainCluster = $cluster->withShare($mainShare);
                $splinterCluster = new PopulationCluster(
                    ideology: $splinterIdeology,
                    share: $splinterShare,
                    radicalization: 0.9, // highly radicalized origin
                    originEventType: 'RADICAL_SPLIT',
                    birthTick: $currentTick
                );

                $newClusters[] = $mainCluster;
                $newClusters[] = $splinterCluster;
                $addedSplits++;
            } else {
                $newClusters[] = clone $cluster;
            }
        }

        return $newClusters;
    }
}
