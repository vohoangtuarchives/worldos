<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\StateVector;

/**
 * HeroSystem
 * 
 * Manages nonlinear shocks and long-term consequences of hero birth.
 */
class HeroSystem
{
    /**
     * Check if a hero emerges this tick based on tension AND current entropy.
     * P(hero) = lambda * T^2
     *
     * Gate: requires meaningful tension + entropy > 0.3 to prevent constant spawning.
     */
    public function checkEmergence(float $tension, int $yearsPerTick, float $currentEntropy = 0.0): bool
    {
        // Heroes only emerge when civilisation is under real stress
        if ($currentEntropy < 0.30) {
            return false; // Society too stable — no desperate heroes needed
        }

        // Reduced lambda: was 0.1, now 0.03 to prevent ~1 spawn/tick
        $lambda = 0.03 * $yearsPerTick;
        $probability = $lambda * ($tension * $tension);

        $rand = mt_rand() / mt_getrandmax();
        return $rand < $probability;
    }

    /**
     * Apply the dual-sided impact of a hero.
     *
     * Heroes now ACT ON ENTROPY FIELD — they are structural correctors,
     * not just narrative cosmetics.
     *
     * Returns:
     * - 'forces': vector F_ext to inject into DynamicalKernel (including ie reduction)
     * - 'tensionRelief': immediate reduction in NarrativeTension
     */
    public function applyHeroImpact(int $heroCount): array
    {
        // Diminishing returns formula: impact = base / (1 + phi * count)
        $phi = 0.2;
        $efficiency = 1.0 / (1.0 + $phi * $heroCount);

        // ── POSITIVE impacts (short-term structural salvation) ─────────
        $c_stab  = 0.4 * $efficiency;   // stability pull-up
        $c_legit = 0.3 * $efficiency;   // legitimacy restoration
        $c_ce    = 0.35 * $efficiency;  // cultural energy boost

        // ── ENTROPY DISSIPATION ────────────────────────────────────────
        // Hero channels civilisational energy to counter disorder.
        // ie force is NEGATIVE = active entropic reduction.
        // Magnitude: -0.50 * efficiency (significant but not instant reset)
        $c_ie_dissip = -0.50 * $efficiency;

        // ── SIDE EFFECTS (long-term Roman collapse pattern) ────────────
        $e_elite = -0.20 * $efficiency; // elite conflict grows (power vacuum)
        $e_ineq  =  0.10 * $efficiency; // inequality rises (heroic privilege)
        $e_sus   = -0.05 * $efficiency; // sustainability strain (campaigns)

        $keys   = StateVector::KEYS;
        $forces = array_fill(0, StateVector::DIMENSIONS, 0.0);

        $forces[array_search('stability',    $keys)] += $c_stab;
        $forces[array_search('legitimacy',   $keys)] += $c_legit;
        $forces[array_search('ce',           $keys)] += $c_ce;
        $forces[array_search('ie',           $keys)] += $c_ie_dissip; // ← KEY: entropy sink
        $forces[array_search('eliteCohesion',$keys)] += $e_elite;
        $forces[array_search('inequality',   $keys)] += $e_ineq;
        $forces[array_search('sustainability',$keys)] += $e_sus;

        return [
            'forces'        => $forces,
            'tensionRelief' => 0.60 * $efficiency, // stronger tension relief
        ];
    }
}
