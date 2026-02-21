<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\CivilizationSnapshot;

/**
 * Modulates the mutation rate (\eta) based on systemic stress.
 * Crisis triggers rapid evolution.
 */
class EvolutionRatePolicy
{
    private float $baseEvoRate;
    private float $stressSensitivity;

    public function __construct(float $baseEvoRate = 0.01, float $stressSensitivity = 0.05)
    {
        $this->baseEvoRate = $baseEvoRate;
        // Sensitivity \alpha controls how heavily stress magnifies mutation
        $this->stressSensitivity = $stressSensitivity; 
    }

    /**
     * \eta = \eta_base * (1 + \alpha * Stress)
     */
    public function determineMutationRate(CivilizationSnapshot $snapshot, float $globalCriticality = 0.0): float
    {
        // Stress comes from low stability, high internal entropy, high inequality
        $internalStress = (1.0 - $snapshot->stability) + $snapshot->internalEntropy + $snapshot->inequality;
        $totalStress = $internalStress + $globalCriticality;

        return $this->baseEvoRate * (1.0 + $this->stressSensitivity * $totalStress);
    }
}
