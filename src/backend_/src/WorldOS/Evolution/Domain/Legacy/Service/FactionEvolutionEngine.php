<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\Faction;
use WorldOS\Evolution\Domain\Legacy\ValueObject\EliteNetwork;

/**
 * FactionEvolutionEngine - Handles the merging and splitting of political factions.
 */
final class FactionEvolutionEngine
{
    private const MAX_FACTIONS = 6;
    private const MIN_FACTIONS = 2;

    public function process(
        array $factions,
        EliteNetwork $eliteNetwork,
        float $structuralEntropy,
        float $legitimacy,
        float $externalThreat,
        float $resourceStress,
        float $shockForecast
    ): array {
        if (empty($factions)) {
            return ['factions' => [], 'network' => $eliteNetwork];
        }

        // 1. Process Split
        $splitResult = $this->applySplit($factions, $eliteNetwork, $structuralEntropy, $legitimacy);
        $factions = $splitResult['factions'];
        $eliteNetwork = $splitResult['network'];

        // 2. Process Merge
        $mergeResult = $this->applyMerge($factions, $eliteNetwork, $externalThreat, $resourceStress, $shockForecast);
        $factions = $mergeResult['factions'];
        $eliteNetwork = $mergeResult['network'];

        // Normalize power shares
        $factions = $this->normalizePowerShares($factions);

        return [
            'factions' => $factions,
            'network' => $eliteNetwork
        ];
    }

    private function applySplit(
        array $factions, 
        EliteNetwork $network, 
        float $structuralEntropy, 
        float $legitimacy
    ): array {
        if (count($factions) >= self::MAX_FACTIONS) {
            return ['factions' => $factions, 'network' => $network];
        }

        $newFactions = [];
        $splitOccurred = false;

        foreach ($factions as $faction) {
            // SplitPressure = (1 - Cohesion) * 0.5 + StructuralEntropy * 0.3 - Legitimacy * 0.2
            $splitPressure = (1.0 - $faction->cohesion) * 0.5 + $structuralEntropy * 0.3 - $legitimacy * 0.2;

            if ($splitPressure > 0.6 && !$splitOccurred && $faction->powerShare > 0.2) {
                // Split into two
                $splitOccurred = true;

                $power1 = $faction->powerShare * 0.6;
                $power2 = $faction->powerShare * 0.4;

                // Splinter ideology drifts slightly
                $idv = $faction->ideology;
                $driftCen = $idv->centralization > 0.5 ? -0.15 : 0.15;
                $driftEco = $idv->economic > 0.5 ? -0.15 : 0.15;
                $idv2 = $idv->applyDrift($driftCen, $driftEco, 0.0, 0.0, 0.0, 0.0);

                $fac1 = new Faction(
                    id: $faction->id . '_a',
                    name: $faction->name . ' (Main)',
                    ideology: clone $idv,
                    powerShare: $power1,
                    cohesion: 0.4, // Cohesion drops after split
                    legitimacyClaim: $faction->legitimacyClaim * 0.8
                );

                $fac2 = new Faction(
                    id: $faction->id . '_b',
                    name: $faction->name . ' (Splinter)',
                    ideology: $idv2,
                    powerShare: $power2,
                    cohesion: 0.6, // Splinter group is often more cohesive initially
                    legitimacyClaim: $faction->legitimacyClaim * 0.5
                );

                $newFactions[] = $fac1;
                $newFactions[] = $fac2;

                // Decrease network rigidity
                $network = $network->withRigidity(max(0.0, $network->networkRigidity - 0.05));
            } else {
                $newFactions[] = $faction;
            }
        }

        return ['factions' => $newFactions, 'network' => $network];
    }

    private function applyMerge(
        array $factions, 
        EliteNetwork $network, 
        float $externalThreat, 
        float $resourceStress, 
        float $shockForecast
    ): array {
        if (count($factions) <= self::MIN_FACTIONS) {
            return ['factions' => $factions, 'network' => $network];
        }

        $mergePressure = $externalThreat * 0.4 + $resourceStress * 0.3 + $shockForecast * 0.3;

        if ($mergePressure < 0.6) {
            return ['factions' => $factions, 'network' => $network];
        }

        // Find closest pair
        $minDist = PHP_FLOAT_MAX;
        $mergePair = null;

        $keys = array_keys($factions);
        for ($i = 0; $i < count($keys); $i++) {
            for ($j = $i + 1; $j < count($keys); $j++) {
                $f1 = $factions[$keys[$i]];
                $f2 = $factions[$keys[$j]];
                
                $dist = $f1->ideology->distanceTo($f2->ideology);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $mergePair = [$keys[$i], $keys[$j]];
                }
            }
        }

        // If distance is acceptable, merge them
        if ($mergePair !== null && $minDist < 0.6) {
            $f1 = $factions[$mergePair[0]];
            $f2 = $factions[$mergePair[1]];
            
            $totalPower = $f1->powerShare + $f2->powerShare;
            $w1 = $f1->powerShare / $totalPower;
            $w2 = $f2->powerShare / $totalPower;

            $newIdeology = new \WorldOS\Evolution\Domain\Legacy\ValueObject\IdeologyVector(
                centralization: $f1->ideology->centralization * $w1 + $f2->ideology->centralization * $w2,
                economic: $f1->ideology->economic * $w1 + $f2->ideology->economic * $w2,
                culture: $f1->ideology->culture * $w1 + $f2->ideology->culture * $w2,
                innovation: $f1->ideology->innovation * $w1 + $f2->ideology->innovation * $w2,
                military: $f1->ideology->military * $w1 + $f2->ideology->military * $w2,
                institution: $f1->ideology->institution * $w1 + $f2->ideology->institution * $w2
            );

            $mergedFaction = new Faction(
                id: $f1->id . '_merged',
                name: $f1->name . ' Coalition',
                ideology: $newIdeology,
                powerShare: $totalPower,
                cohesion: ($f1->cohesion * $w1) + ($f2->cohesion * $w2),
                legitimacyClaim: max($f1->legitimacyClaim, $f2->legitimacyClaim)
            );

            unset($factions[$mergePair[0]]);
            unset($factions[$mergePair[1]]);
            $factions[] = $mergedFaction;
            $factions = array_values($factions);

            // Increase network rigidity
            $network = $network->withRigidity(min(1.0, $network->networkRigidity + 0.10));
        }

        return ['factions' => $factions, 'network' => $network];
    }

    private function normalizePowerShares(array $factions): array
    {
        $totalPower = 0.0;
        foreach ($factions as $faction) {
            $totalPower += $faction->powerShare;
        }

        if ($totalPower <= 0.0) {
            return $factions;
        }

        $normalized = [];
        foreach ($factions as $faction) {
            $normalized[] = new Faction(
                id: $faction->id,
                name: $faction->name,
                ideology: $faction->ideology,
                powerShare: $faction->powerShare / $totalPower,
                cohesion: $faction->cohesion,
                legitimacyClaim: $faction->legitimacyClaim,
                resourceControl: $faction->resourceControl,
                militaryInfluence: $faction->militaryInfluence
            );
        }

        return $normalized;
    }
}
