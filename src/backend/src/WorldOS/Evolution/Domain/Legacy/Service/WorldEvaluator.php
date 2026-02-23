<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

/**
 * WorldEvaluator
 * Evaluates a simulation seed after an epoch (e.g. 1000 years) to determine its "Interestingness".
 * This is used to filter out boring parallel simulations (e.g., world dies early, or stays in equilibrium forever).
 */
class WorldEvaluator
{
    /**
     * Calculate how compelling the generated history was.
     * Higher score means more action, tension, rise/fall dynamics, or unique traits.
     * 
     * @param array $chronicleData The summarized timeline data generated during simulation
     * @return float Score from 0.0 to 1.0 (or theoretically higher for legendary seeds)
     */
    public function evaluateInterestingness(array $chronicleData): float
    {
        $score = 0.0;
        
        $totalYears = $chronicleData['total_years'] ?? 0;
        if ($totalYears < 100) {
            return 0.1; // Premature death, boring seed
        }

        $epochResets = $chronicleData['epoch_resets'] ?? 0;
        $maxTech = $chronicleData['max_technological_level'] ?? 0.0;
        $majorWars = $chronicleData['major_conflicts'] ?? 0;
        $heroCount = $chronicleData['total_heroes_emerged'] ?? 0;

        // Dynamics are interesting. 1 or 2 epoch resets is great (cycles of history).
        // 0 means it never fell (boring utopia or flatline). > 5 means it's chaotic noise.
        if ($epochResets >= 1 && $epochResets <= 3) {
            $score += 0.3;
        } elseif ($epochResets > 3) {
            $score += 0.1;
        }

        // Tech scaling
        $score += min(0.3, $maxTech * 0.15); // E.g. Tech 2.0 = +0.3

        // Conflict density (appropriation friction)
        if ($majorWars > 5) {
            $score += 0.2;
        }

        // Hero emergence means the system hit high tension and spawned great figures
        $score += min(0.2, $heroCount * 0.05);

        return $score;
    }
}
