<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Services;

use WorldOS\Domains\Evolution\ValueObjects\StateVector;

/**
 * HeroSystem
 * 
 * Manages nonlinear shocks and long-term consequences of hero birth.
 */
class HeroSystem
{
    /**
     * Check if a hero emerges this tick based on tension.
     * P(hero) = lambda * T^2
     */
    public function checkEmergence(float $tension, int $yearsPerTick): bool
    {
        // High threshold. If tension is 0.8, P(hero) is 0.64 * lambda
        $lambda = 0.1 * $yearsPerTick; 
        $probability = $lambda * ($tension * $tension);
        
        $rand = mt_rand() / mt_getrandmax();
        return $rand < $probability;
    }

    /**
     * Apply the dual-sided impact of a hero.
     * Returns:
     * - 'forces': vector F_ext to inject into DynamicalKernel
     * - 'tensionRelief': immediate reduction in NarrativeTension
     */
    public function applyHeroImpact(int $heroCount): array
    {
        // Diminishing returns formula: impact = base / (1 + phi * count)
        $phi = 0.2;
        $efficiency = 1.0 / (1.0 + $phi * $heroCount);

        // Immediate positive impacts (Short-term salvation)
        $c_stab = 0.4 * $efficiency;
        $c_legit = 0.3 * $efficiency;
        $c_ce = 0.5 * $efficiency;

        // Hidden side effects (Long-term fragmentation & Rome pattern)
        // Elite conflict increases as heroes demand power
        $e_elite = -0.2 * $efficiency; 
        // Inequality increases because of heroic privileges
        $e_ineq = 0.1 * $efficiency;   
        
        // Strain on sustainability due to intense campaigns
        $e_sus = -0.05 * $efficiency;

        $keys = StateVector::KEYS;
        $forces = array_fill(0, StateVector::DIMENSIONS, 0.0);

        $forces[array_search('stability', $keys)] += $c_stab;
        $forces[array_search('legitimacy', $keys)] += $c_legit;
        $forces[array_search('ce', $keys)] += $c_ce;
        
        $forces[array_search('eliteCohesion', $keys)] += $e_elite; // Negative force
        $forces[array_search('inequality', $keys)] += $e_ineq;     // Positive force (inequality grows)
        $forces[array_search('sustainability', $keys)] += $e_sus;  // Negative force

        return [
            'forces' => $forces,
            'tensionRelief' => 0.5 * $efficiency // Drops tension significantly initially
        ];
    }
}
