<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;

/**
 * EcologicalPressureEngine - Calculates Resource Pressure and Complexity Costs
 * preventing infinite growth and enforcing historical/ecological cycles.
 */
final class EcologicalPressureEngine
{
    /**
     * @return array{resourcePressure: float, complexityCost: float}
     */
    public function calculatePressures(CivilizationSnapshot $civ): array
    {
        // Resource Pressure: Demographics + Wealth vs Technology
        // Using prosperity and expansionism as proxies for population & economic load
        $rawResourceStress = ($civ->prosperity * 0.4) + ($civ->expansionism * 0.4) - ($civ->technologicalLevel * 0.3);
        $resourcePressure = max(0.0, min(1.0, $rawResourceStress));

        // Complexity Cost: Bureaucratic and structural overhead of running a massive empire
        // ComplexityCost ∝ log(Centralization + Tech + Territory)
        
        // Find dominant logic
        $centralization = 0.5;
        if (!empty($civ->factions)) {
            $dominant = null;
            $maxP = -1.0;
            foreach ($civ->factions as $f) {
                if ($f->powerShare > $maxP) {
                    $maxP = $f->powerShare;
                    $dominant = $f;
                }
            }
            if ($dominant) {
                $centralization = $dominant->ideology->centralization;
            }
        }

        $complexityBase = $centralization + $civ->technologicalLevel + $civ->expansionism + 1.0; // +1.0 to ensure log(>=1) >= 0
        $complexityCost = max(0.0, log($complexityBase) * 0.3);

        return [
            'resourcePressure' => $resourcePressure,
            'complexityCost' => min(1.0, $complexityCost)
        ];
    }
}
