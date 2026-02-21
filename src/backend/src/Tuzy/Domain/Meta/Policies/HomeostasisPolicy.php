<?php

namespace Tuzy\Domain\Meta\Policies;

class HomeostasisPolicy
{
    // Coefficient of Restoring Force (Gamma)
    // Higher = pull back stronger
    private float $gamma = 0.02;

    // Equilibrium Center (Default)
    private array $defaultEquilibrium = [
        'order' => 0.5,
        'chaos' => 0.5,
        'expansion' => 0.5,
        'consolidation' => 0.5,
        'diversity' => 0.5,
    ];

    public function __construct(float $gamma = 0.02)
    {
        $this->gamma = $gamma;
    }

    /**
     * Calculate vector delta needed to restore balance
     */
    public function calculateRestoringForce(array $currentVector, int $eraIndex): array
    {
        $forces = [];
        $equilibrium = $this->getEquilibriumTarget($eraIndex);

        foreach ($currentVector as $axis => $value) {
            $target = $equilibrium[$axis] ?? 0.5;
            // Force = (Target - Current) * Gamma
            // If Current > Target, Force is negative (pull down)
            // If Current < Target, Force is positive (pull up)
            $forces[$axis] = ($target - $value) * $this->gamma;
        }

        return $forces;
    }

    /**
     * Get the target equilibrium for a given Era.
     * Can be modified by long-term drift or era specifics.
     */
    private function getEquilibriumTarget(int $eraIndex): array
    {
        // Future: Era could shift equilibrium center
        // For now, return constant center
        return $this->defaultEquilibrium;
    }
}
