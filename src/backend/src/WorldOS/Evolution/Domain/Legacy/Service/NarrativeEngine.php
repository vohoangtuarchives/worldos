<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;
use WorldOS\Evolution\Domain\Legacy\ValueObject\StateVector;

/**
 * NarrativeEngine
 * 
 * Manages the "Drama" of the simulation.
 * Calculates Narrative Tension and Softmax Phase Arc.
 */
class NarrativeEngine
{
    /**
     * Update Tension T_t+1
     * @return array<string, float> ['short' => T_short, 'long' => T_long, 'total' => T_total]
     */
    public function updateTension(
        float $currentShortTension, 
        float $currentLongTension,
        StateVector $state, 
        float $cosmicEntropy, 
        float $heroImpactThisTick = 0.0
    ): array {
        $keys = StateVector::KEYS;
        $v = $state->values;

        // Extract values
        $stab = $v[array_search('stability', $keys)];
        $p = $v[array_search('prosperity', $keys)];
        $ineq = $v[array_search('inequality', $keys)];

        // Strain proxy (Inverse of Stability * Prosperity)
        $strain = 1.0 - ($stab * 0.5 + $p * 0.5);

        // Tension Equation
        $alpha = 0.3; // Strain
        $beta = 0.2;  // Entropy
        $gamma = 0.2; // Inequality
        $delta = 0.3; // Stability relieves tension
        $eta = 0.1;   // Prosperity relieves tension
        $kappa = 0.4; // Hero impact relieves tension instantly

        $tensionPressure = ($alpha * $strain) 
            + ($beta * $cosmicEntropy) 
            + ($gamma * $ineq) 
            - ($delta * $stab) 
            - ($eta * $p) 
            - ($kappa * $heroImpactThisTick);

        // Momentum / Elasticity
        $u_short = 0.2; // Fast reacting
        
        $newShort = (1 - $u_short) * $currentShortTension + $u_short * $tensionPressure;
        
        // Long wave: civilisational trauma with NATURAL DECAY
        // Issue before: epsilon growth (0.002 * 15000 ticks = +3.0) with no decay → always saturates to 1.0.
        // Fix: add exponential decay proportional to stability+prosperity (healing during good eras)
        $epsilon = 0.0012;   // Growth rate per tick (reduced from 0.002)
        $decayRate = 0.0040; // Natural forgetting per tick
        $healingBonus = ($stab * 0.4 + $p * 0.4) * 0.006; // Therapy: stability+prosperity heal trauma
        $goldenAgeEffect = ($p > 0.8 && $stab > 0.8) ? 0.012 : 0.0; // Golden Age: strong healing
        
        $newLong = $currentLongTension
            + ($epsilon * $cosmicEntropy)                 // accumulate under cosmic pressure
            - ($decayRate * $currentLongTension)          // natural exponential decay
            - $healingBonus                               // prosperity heals slowly
            - $goldenAgeEffect;                           // golden age heals fast

        $newShort = max(0.0, min(1.0, $newShort));
        $newLong  = max(0.0, min(1.0, $newLong));
        
        // Blend total
        $total = 0.7 * $newShort + 0.3 * $newLong;

        return [
            'short' => $newShort,
            'long'  => $newLong,
            'total' => max(0.0, min(1.0, $total))
        ];
    }
    
    /**
     * Evaluate the extremely rare event of Golden Transcendence
     * 
     * @return null|array Event array.
     */
    public function evaluateTranscendence(CivilizationSnapshot $civ, string $seed): ?array
    {
        $lambda = 0.001; // Base chance extremely low
        $stab = $civ->stability;
        $ce = $civ->culturalEnergy;
        $entropy = $civ->internalEntropy;
        $tech = $civ->technologicalLevel;
        $tension = $civ->narrativeTension;
        
        if ($stab < 0.7 || $tech < 0.5 || $ce < 0.5 || $entropy > 0.4) {
            return null; // Strict conditions
        }
        
        $prob = $lambda * $stab * $ce * (1.0 - $entropy) * pow($tech, 1.5) * exp(-$tension);
        
        $rng = hexdec(substr(md5($seed . '_transc'), 0, 4)) / 0xffff;
        if ($rng < $prob) {
            return [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'transcendence',
                'name' => 'Golden Transcendence',
                'intensity' => 1.0,
                'description' => 'The civilization has achieved a profound state of self-awareness and unity, entering the Age of Wisdom.',
                'tensionRelief' => 0.0,
                'forces' => []
            ];
        }
        
        return null;
    }

    /**
     * Calculate 4-Phase Weights using Softmax with k factor
     * Growth, Stress, Decline, Collapse
     */
    public function computePhaseWeights(float $tension): array
    {
        $k = 8.0; // Sharpness
        
        $phases = [
            'growth' => 0.2, // Peaks when tension is ~0.2
            'stress' => 0.5,
            'decline' => 0.7,
            'collapse' => 0.9,
        ];

        $weights = [];
        $sum = 0.0;

        foreach ($phases as $p => $theta) {
            $val = exp(-$k * abs($tension - $theta));
            $weights[$p] = $val;
            $sum += $val;
        }

        // Normalize
        $normalized = [];
        foreach ($weights as $p => $val) {
            $normalized[$p] = $sum > 0 ? $val / $sum : 0.0;
        }

        return $normalized;
    }
}
