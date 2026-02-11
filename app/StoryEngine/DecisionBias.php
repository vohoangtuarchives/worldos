<?php

namespace App\StoryEngine;

class DecisionBias
{
    /**
     * Adjust the base risk tolerance based on faction history.
     * 
     * @param FactionState $faction
     * @param float $baseRisk 0.0 to 1.0 (Higher is riskier)
     * @return float Adjusted risk
     */
    public static function adjustRisk(
        FactionState $faction,
        float $baseRisk
    ): float {
        $failures = array_sum($faction->memory->failureCounter);
        $success = array_sum($faction->memory->successCounter);

        // If failures outweigh success, become more conservative
        if ($failures > $success) {
            return max(0.05, $baseRisk - 0.2);
        }

        // If success typically happens, become bolder
        return min(0.9, $baseRisk + 0.1);
    }
}
