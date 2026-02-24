<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;

/**
 * Calculates the logistic probability of a global epoch reset based on accumulated stress.
 */
class EpochResetEngine
{
    private float $k;
    private float $criticalThreshold;

    public function __construct(float $k = 5.0, float $criticalThreshold = 2.0)
    {
        $this->k = $k;
        $this->criticalThreshold = $criticalThreshold;
    }

    /**
     * @param array<string, CivilizationSnapshot> $snapshots
     */
    public function calculateGlobalCriticality(array $snapshots): float
    {
        $totalStress = 0.0;
        $count = count($snapshots);
        if ($count === 0) {
            return 0.0;
        }

        foreach ($snapshots as $snapshot) {
            // C = Inequality + Entropy + Inverse Stability
            $stress = $snapshot->inequality + $snapshot->internalEntropy + (1.0 - $snapshot->stability);
            $totalStress += $stress;
        }

        // Add centralization factor (if 1 civ owns >50% of regions, centralization increases)
        // Simplified: just average stress for now
        return $totalStress / $count;
    }

    /**
     * Determines whether an epoch reset occurs this tick using a logistic function.
     * P(reset) = 1 / (1 + e^{-k(C - C0)})
     */
    public function shouldTriggerReset(float $globalCriticality): bool
    {
        if ($globalCriticality < $this->criticalThreshold - 1.0) {
            return false; // Fast exit if way below threshold
        }

        $probability = 1.0 / (1.0 + exp(-$this->k * ($globalCriticality - $this->criticalThreshold)));
        
        $roll = mt_rand() / mt_getrandmax();
        return $roll < $probability;
    }
}
