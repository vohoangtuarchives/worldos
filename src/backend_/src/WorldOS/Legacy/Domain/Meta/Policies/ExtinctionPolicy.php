<?php

namespace WorldOS\Legacy\Domain\Meta\Policies;

use WorldOS\Legacy\Domain\Meta\Aggregates\MetaLayer;

class ExtinctionPolicy
{
    // Thresholds
    private float $maxChaos = 0.9;
    private float $maxEntropyPressure = 0.8;
    private float $minStability = 0.1;

    /**
     * Check if a Mass Extinction event should be triggered based on Meta State
     */
    public function shouldTriggerExtinction(MetaLayer $metaLayer): bool
    {
        // 1. Extreme Chaos
        if ($metaLayer->chaosPool > 150.0) { // Arbitrary scale
            return true;
        }

        // 2. Ideological Collapse (Monoculture)
        $maxIdeology = max($metaLayer->ideologyVector);
        if ($maxIdeology > 0.95) {
            // Extinction to restore diversity
            return true;
        }

        // 3. Low Stability
        if ($metaLayer->stabilityIndex < $this->minStability) {
            return true;
        }

        return false;
    }

    /**
     * Calculate severity of extinction (0.0 - 1.0)
     */
    public function calculateSeverity(MetaLayer $metaLayer): float
    {
        $severity = 0.0;
        
        if ($metaLayer->chaosPool > 100.0) {
            $severity += 0.3;
        }
        
        $maxIdeology = max($metaLayer->ideologyVector);
        if ($maxIdeology > 0.8) {
            $severity += ($maxIdeology - 0.8) * 2; // Up to 0.4
        }
        
        return min(1.0, max(0.2, $severity));
    }
}
