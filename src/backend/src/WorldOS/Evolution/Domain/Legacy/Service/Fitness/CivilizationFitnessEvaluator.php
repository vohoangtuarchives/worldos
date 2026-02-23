<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service\Fitness;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;

class CivilizationFitnessEvaluator
{
    /**
     * Calculates the "Narrative Fitness" of a civilization.
     * Higher score means more interesting historical output.
     */
    public function evaluate(CivilizationSnapshot $snapshot): float
    {
        // 1. Conflict Intensity (0.0 - 1.0)
        // High narrative tension and balanced military pressure/internal entropy contribute to "drama".
        $tensionScore = $snapshot->narrativeTension;
        $chaosScore = ($snapshot->internalEntropy + $snapshot->militaryPressure) / 2.0;
        $intensity = ($tensionScore * 0.7 + $chaosScore * 0.3);

        // 2. Transformational Depth (Achievement)
        // Progress in tech and power stage.
        $techAchievement = $snapshot->technologicalLevel / 2.0; // max 2.0
        $powerAchievement = $snapshot->powerStage->level() / 5.0; // max 5
        $depth = ($techAchievement * 0.5 + $powerAchievement * 0.5);

        // 3. Heroic Legacy
        // Number of heroes born indicates pivotal moments.
        $heroicImpact = min(1.0, $snapshot->heroCount * 0.1);

        // 4. Resilience & Survival
        // Capability to survive stress (Historical Legacy + Stability)
        $survival = ($snapshot->historicalLegacy * 0.6 + $snapshot->resilience * 0.4);

        // Final Narrative Fitness Formula
        // We weight Intensity and Heroic Impact higher as they drive the "Story".
        $fitness = (
            $intensity * 0.35 + 
            $depth * 0.20 + 
            $heroicImpact * 0.25 + 
            $survival * 0.20
        );

        return round($fitness, 4);
    }
}
