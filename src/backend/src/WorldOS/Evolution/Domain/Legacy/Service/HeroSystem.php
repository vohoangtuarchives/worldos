<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\StateVector;

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
    public function checkEmergence(float $tension, int $yearsPerTick, float $currentEntropy = 0.0, ?string $phase = null): bool
    {
        // Heroes only emerge when civilisation is under real stress
        if ($currentEntropy < 0.30) {
            return false; // Society too stable — no desperate heroes needed
        }

        // Reduced lambda: was 0.1, now 0.03 to prevent ~1 spawn/tick
        $lambda = 0.03 * $yearsPerTick;
        
        if ($phase === \WorldOS\Evolution\Domain\Legacy\Service\CivilizationPhaseDetector::PHASE_CHAOS) {
            $lambda *= 1.5; // Chaos Basin breeds 1.5x more heroes
        }

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
        $c_stab  = 0.40 * $efficiency;   // stability pull-up
        $c_legit = 0.30 * $efficiency;   // legitimacy restoration
        $c_ce    = 0.35 * $efficiency;   // cultural energy boost
        $c_sc    = 0.25 * $efficiency;   // spiritual cohesion surge (messianic figure)
        $c_tech  = 0.15 * $efficiency;   // technological or tactical innovation
        $c_prosp = 0.20 * $efficiency;   // prosperity bump from initial conquests/reforms
        $c_mobil = 0.25 * $efficiency;   // social mobility rises (meritocracy of the hero)
        $c_info  = 0.15 * $efficiency;   // information flow improvements (new networks)
        $c_myst  = 0.10 * $efficiency;   // adds to the arcane/mystery of the epoch
        $c_legacy= 0.30 * $efficiency;   // massive boost to historical legacy
        $c_curve = 0.40 * $efficiency;   // massively bends the historical field curvature

        // ── ENTROPY DISSIPATION ────────────────────────────────────────
        $c_ie_dissip = -0.50 * $efficiency; // active entropic reduction

        // ── SIDE EFFECTS (long-term Roman collapse pattern) ────────────
        $e_elite = -0.25 * $efficiency; // elite conflict: old guard vs new hero
        $e_ineq  =  0.15 * $efficiency; // inequality rises (new aristocratic class forms)
        $e_sus   = -0.10 * $efficiency; // sustainability strain (massive campaigns)
        $e_mp    =  0.30 * $efficiency; // military pressure spikes
        $e_exp   =  0.25 * $efficiency; // expansionism fires up

        $keys   = StateVector::KEYS;
        $forces = array_fill(0, StateVector::DIMENSIONS, 0.0);

        // Map all 17 dimensions
        $forces[array_search('ce',            $keys)] += $c_ce;
        $forces[array_search('sc',            $keys)] += $c_sc;
        $forces[array_search('tech',          $keys)] += $c_tech;
        $forces[array_search('stability',     $keys)] += $c_stab;
        $forces[array_search('prosperity',    $keys)] += $c_prosp;
        $forces[array_search('mp',            $keys)] += $e_mp;
        $forces[array_search('ie',            $keys)] += $c_ie_dissip;
        $forces[array_search('legitimacy',    $keys)] += $c_legit;
        $forces[array_search('eliteCohesion', $keys)] += $e_elite;
        $forces[array_search('inequality',    $keys)] += $e_ineq;
        $forces[array_search('sustainability',$keys)] += $e_sus;
        $forces[array_search('mystery',       $keys)] += $c_myst;
        $forces[array_search('legacy',        $keys)] += $c_legacy;
        $forces[array_search('expansion',     $keys)] += $e_exp;
        $forces[array_search('info',          $keys)] += $c_info;
        $forces[array_search('mobility',      $keys)] += $c_mobil;
        $forces[array_search('curvature',     $keys)] += $c_curve;

        return [
            'forces'        => $forces,
            'tensionRelief' => 0.60 * $efficiency, // stronger tension relief
        ];
    }
}
