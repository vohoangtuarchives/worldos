<?php

declare(strict_types=1);

namespace App\Modules\Simulation\Services\Cascade;

use App\Modules\WorldTemplate\Contracts\CascadeEngineInterface;
use App\Modules\WorldTemplate\ValueObjects\CascadeThresholds;
use App\Modules\Shared\ValueObjects\CascadeStateVector;
use App\Modules\Shared\ValueObjects\LawVector;

/**
 * Cascade Evolution Engine — SimulationEngine Implementation.
 *
 * 5-layer cascade: Physics → Chemistry → Biology → Cognition → Culture.
 * Each upper layer can only emerge when its lower layer exceeds the activation threshold.
 *
 * Pure computation — NO Laravel dependencies, NO side effects.
 */
final class CascadeEvolutionEngine implements CascadeEngineInterface
{
    public function evolve(
        CascadeStateVector $state,
        LawVector $law,
        CascadeThresholds $thresholds,
    ): CascadeStateVector {
        $deltas = [
            'physics' => 0.0,
            'chemistry' => 0.0,
            'biology' => 0.0,
            'cognition' => 0.0,
            'culture' => 0.0,
        ];

        // Physics layer — always active, self-sustaining
        $deltas['physics'] = $law->energyStability * (1.0 - $state->physics) * $law->selfOrganization * 0.1
            - $law->collapseProbability * $state->physics * 0.05;

        // Chemistry layer — activates when P > τ₁
        if ($state->physics > $thresholds->physicsToChemistry) {
            $activationStrength = ($state->physics - $thresholds->physicsToChemistry)
                / (1.0 - $thresholds->physicsToChemistry);

            $deltas['chemistry'] = $law->entropyGrowth * $state->physics * (1.0 - $state->chemistry) * 0.08
                * $activationStrength
                - (1.0 - $law->energyStability) * $state->chemistry * 0.02;
        } else {
            $deltas['chemistry'] = -$state->chemistry * 0.05;
        }

        // Biology layer — activates when C > τ₂
        if ($state->chemistry > $thresholds->chemistryToBiology) {
            $activationStrength = ($state->chemistry - $thresholds->chemistryToBiology)
                / (1.0 - $thresholds->chemistryToBiology);

            $deltas['biology'] = $law->stabilityBasinDepth * $state->chemistry * (1.0 - $state->biology) * 0.06
                * $activationStrength
                * $law->abiogenesis
                - $law->collapseProbability * $state->biology * 0.03;
        } else {
            $deltas['biology'] = -$state->biology * 0.04;
        }

        // Cognition layer — activates when B > τ₃
        if ($state->biology > $thresholds->biologyCognition) {
            $activationStrength = ($state->biology - $thresholds->biologyCognition)
                / (1.0 - $thresholds->biologyCognition);

            $socialFactor = $law->cognitiveCeiling * (1.0 - $state->cognition);
            $deltas['cognition'] = $law->adaptationEfficiency * $state->biology * $socialFactor * 0.05
                * $activationStrength
                - $law->mutationVolatility * $state->cognition * 0.02;
        } else {
            $deltas['cognition'] = -$state->cognition * 0.03;
        }

        // Culture layer — activates when N > τ₄
        if ($state->cognition > $thresholds->cognitionToCulture) {
            $activationStrength = ($state->cognition - $thresholds->cognitionToCulture)
                / (1.0 - $thresholds->cognitionToCulture);

            $techDrive = $law->memoryPersistence * $law->techAccumulationRate * $state->cognition * 0.04;
            $metaFeedback = $law->metaSystemAwareness * $state->culture * (1.0 - $state->culture) * 0.03;
            $deltas['culture'] = ($techDrive + $metaFeedback) * $activationStrength
                - (1.0 - $law->memoryPersistence) * $state->culture * 0.02;
        } else {
            $deltas['culture'] = -$state->culture * 0.02;
        }

        // Cascade reverse: lower layer collapse → upper decay
        $this->applyCascadeReverse($state, $thresholds, $deltas);

        return $state->withDelta($deltas);
    }

    /**
     * @param array<string, float> &$deltas
     */
    private function applyCascadeReverse(
        CascadeStateVector $state,
        CascadeThresholds $thresholds,
        array &$deltas,
    ): void {
        if ($state->physics < $thresholds->physicsToChemistry && $state->chemistry > 0.1) {
            $deltas['chemistry'] -= ($thresholds->physicsToChemistry - $state->physics) * 0.1;
        }

        if ($state->chemistry < $thresholds->chemistryToBiology && $state->biology > 0.1) {
            $deltas['biology'] -= ($thresholds->chemistryToBiology - $state->chemistry) * 0.08;
        }

        if ($state->biology < $thresholds->biologyCognition && $state->cognition > 0.1) {
            $deltas['cognition'] -= ($thresholds->biologyCognition - $state->biology) * 0.06;
        }

        if ($state->cognition < $thresholds->cognitionToCulture && $state->culture > 0.1) {
            $deltas['culture'] -= ($thresholds->cognitionToCulture - $state->cognition) * 0.05;
        }
    }
}
