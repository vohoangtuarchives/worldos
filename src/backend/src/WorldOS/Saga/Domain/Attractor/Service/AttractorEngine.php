<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Attractor\Service;

use WorldOS\Saga\Domain\Attractor\ValueObject\AttractorResult;
use WorldOS\Saga\Domain\Attractor\ValueObject\AttractorType;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroCyclePhase;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroState;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

/**
 * AttractorEngine — Determines the emergent gravity of narrative endings
 * without relying on a pre-authored branching tree.
 */
final class AttractorEngine
{
    /**
     * Evaluates the absolute final states of the macros (Universe) and the micro (Hero)
     * to calculate the affinity scores for the 4 core systemic Attractors.
     * The attractor with the highest gravity acts as the ending.
     */
    public function evaluate(StateVector $universe, HeroState $hero, string $heroDominantDimension): AttractorResult
    {
        // 1. Extract critical macro signals
        $entropy   = $universe->get(StateVector::DIMENSION_ENTROPY);
        $tension   = $universe->get(StateVector::DIMENSION_COSMIC_TENSION);
        // Stability is inversely proportional to tension and entropy
        $stability = max(0.0, 1.0 - (($entropy + $tension) / 2));
        $dominantMacro = $universe->get($heroDominantDimension);

        // 2. Extract critical micro signals
        $stress     = $hero->getStressLevel();
        $conviction = $hero->getConviction();
        $phase      = $hero->getCyclePhase();

        // 3. Compute raw gravitational affinities [0.0 - 1.0+]
        $scores = [
            AttractorType::COLLAPSE->value    => $this->scoreCollapse($entropy, $stress, $phase),
            AttractorType::REWRITE->value     => $this->scoreRewrite($stability, $conviction, $phase),
            AttractorType::CONVERGENCE->value => $this->scoreConvergence($stability, $stress, $phase),
            AttractorType::ESCAPE->value      => $this->scoreEscape($tension, $conviction, $dominantMacro),
        ];

        // 4. Resolve the winner
        $selectedType = AttractorType::COLLAPSE; // default baseline worst case
        $maxScore = -1.0;

        foreach ($scores as $typeString => $score) {
            if ($score > $maxScore) {
                $maxScore = $score;
                $selectedType = AttractorType::from($typeString);
            }
        }

        return AttractorResult::create($selectedType, $scores);
    }

    /**
     * Triggered by: Critical Universe Entropy + Saturated Hero Stress + Collapse Phase.
     */
    private function scoreCollapse(float $macroEntropy, float $microStress, HeroCyclePhase $phase): float
    {
        $score = ($macroEntropy * 0.5) + ($microStress * 0.3);
        
        if ($phase === HeroCyclePhase::COLLAPSE) {
            $score += 0.4;
        }

        return $score;
    }

    /**
     * Triggered by: Low Universe Stability + Extreme Hero Conviction + Breakthrough Phase.
     */
    private function scoreRewrite(float $macroStability, float $microConviction, HeroCyclePhase $phase): float
    {
        $instability = 1.0 - $macroStability;
        $score = ($instability * 0.4) + ($microConviction * 0.4);

        if ($phase === HeroCyclePhase::BREAKTHROUGH) {
            $score += 0.5; // Massive gravity pulling towards a rewrite ending
        }

        return $score;
    }

    /**
     * Triggered by: High Universe Stability + Low Hero Stress + Restabilization/Accumulation.
     */
    private function scoreConvergence(float $macroStability, float $microStress, HeroCyclePhase $phase): float
    {
        $peace = 1.0 - $microStress;
        $score = ($macroStability * 0.5) + ($peace * 0.3);

        if ($phase === HeroCyclePhase::RESTABILIZATION || $phase === HeroCyclePhase::ACCUMULATION) {
            $score += 0.3;
        }

        return $score;
    }

    /**
     * Triggered by: High Universe Tension + High Hero Conviction + Suppressed Dominant Dimension.
     */
    private function scoreEscape(float $macroTension, float $microConviction, float $macroDominantDim): float
    {
        $suppression = 1.0 - $macroDominantDim; // The world rejects the hero's core nature
        $score = ($macroTension * 0.3) + ($microConviction * 0.4) + ($suppression * 0.3);

        // Escape is a radical choice, needs very high conviction and high suppression to trigger heavily
        if ($microConviction > 0.8 && $suppression > 0.7) {
            $score += 0.4;
        }

        return $score;
    }
}
