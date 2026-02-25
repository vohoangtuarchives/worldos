<?php

declare(strict_types=1);

namespace App\Modules\Simulation\Services\Feasibility;

use App\Modules\WorldTemplate\Contracts\FeasibilityCheckerInterface;
use App\Modules\WorldTemplate\ValueObjects\FeasibilityResult;
use App\Modules\Shared\ValueObjects\LawVector;

/**
 * Feasibility Checker — SimulationEngine Implementation.
 *
 * Validates F(θ) — determines whether a LawVector can produce a viable Universe.
 * 6 constraints derived from CIVILIZATION_ENGINE_LAW_SPACE.md.
 *
 * Pure computation — NO Laravel dependencies, NO side effects.
 */
final class FeasibilityChecker implements FeasibilityCheckerInterface
{
    public function check(LawVector $law): FeasibilityResult
    {
        $violations = [];
        $scores = [];

        // C1: Material stability — S₁ = θ₃·θ₆·θ₈
        $materialStability = $law->energyStability * $law->matterComplexity * $law->stabilityBasinDepth;
        $scores['material_stability'] = $materialStability;
        if ($materialStability < 0.2) {
            $violations[] = "C1: Material stability S₁={$materialStability} < 0.2";
        }

        // C2: Entropy bound — θ₅ < θ₈ + 0.3
        $entropyBound = $law->stabilityBasinDepth + 0.3;
        $scores['entropy_bound'] = $entropyBound - $law->entropyGrowth;
        if ($law->entropyGrowth >= $entropyBound) {
            $violations[] = "C2: Entropy growth θ₅={$law->entropyGrowth} ≥ bound {$entropyBound}";
        }

        // C3: Biological viability — θ₁₀·θ₁₂ > 0.15
        $bioViability = $law->abiogenesis * $law->adaptationEfficiency;
        $scores['bio_viability'] = $bioViability;
        if ($bioViability <= 0.15) {
            $violations[] = "C3: Bio viability θ₁₀·θ₁₂={$bioViability} ≤ 0.15";
        }

        // C4: Cognitive cap — θ₁₃ ≤ 0.8·θ₃ + 0.2·θ₄
        $cognitiveCap = 0.8 * $law->energyStability + 0.2 * $law->interactionStrength;
        $scores['cognitive_cap'] = $cognitiveCap;
        if ($law->cognitiveCeiling > $cognitiveCap) {
            $violations[] = "C4: Cognitive ceiling θ₁₃={$law->cognitiveCeiling} > cap {$cognitiveCap}";
        }

        // C5: Cultural foundation — θ₁₄ > 0 requires θ₁₃ > 0.2
        if ($law->mythFormation > 0.0 && $law->cognitiveCeiling <= 0.2) {
            $scores['cultural_foundation'] = 0.0;
            $violations[] = "C5: Myth formation θ₁₄={$law->mythFormation} requires θ₁₃ > 0.2";
        } else {
            $scores['cultural_foundation'] = 1.0;
        }

        // C6: Tech requires cognition — θ₁₆ > 0 requires θ₁₃ > 0.3
        if ($law->techAccumulationRate > 0.0 && $law->cognitiveCeiling <= 0.3) {
            $scores['tech_foundation'] = 0.0;
            $violations[] = "C6: Tech accumulation θ₁₆={$law->techAccumulationRate} requires θ₁₃ > 0.3";
        } else {
            $scores['tech_foundation'] = 1.0;
        }

        if (empty($violations)) {
            return FeasibilityResult::pass($scores);
        }

        return FeasibilityResult::fail($violations, $scores);
    }
}
