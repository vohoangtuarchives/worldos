<?php

declare(strict_types=1);

namespace App\WorldOS\SimulationEngine\Physics;

use App\WorldOS\Cosmology\Contracts\PhysicsEngineInterface;
use App\WorldOS\Shared\ValueObjects\LawVector;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * Base Physics Engine — SimulationEngine Implementation.
 *
 * Implements the core differential equations for WorldStateVector evolution.
 * This is the "Left Brain" deterministic core — NO randomness here.
 *
 * Equations from WORLDOS_BACKEND_DOCUMENTATION.md & CIVILIZATION_ENGINE_LAW_SPACE.md:
 *
 *   dEntropy    = (Inequality² × 0.05) + (Trauma × 0.03) - (Innovation × 0.02)
 *   dOrder      = (Cohesion × 0.04) - (Entropy × 0.03) - (Trauma × 0.02)
 *   dInnovation = (Order × 0.03) × (1 - Entropy) - (Inequality × 0.02) + (θ₁₆ × 0.01)
 *   dCohesion   = (Order × 0.02) - (Inequality × 0.04) - (Trauma × 0.03)
 *   dInequality = (Innovation × 0.02) - (Cohesion × 0.03) + (Entropy × 0.01)
 *   dTrauma     = collapse_pressure - (Cohesion × 0.02) - (Order × 0.01)
 *
 * Pure computation — NO Laravel dependencies, NO side effects.
 */
final class BasePhysicsEngine implements PhysicsEngineInterface
{
    public function evolve(WorldStateVector $state, LawVector $law): WorldStateVector
    {
        // dEntropy: disorder growth from inequality and trauma, reduced by innovation
        $dEntropy = ($state->inequality ** 2 * 0.05)
            + ($state->trauma * 0.03)
            - ($state->innovation * 0.02)
            + ($law->entropyGrowth * 0.01);

        // Critical entropy feedback — above 0.85, entropy accelerates
        if ($state->entropy > 0.85) {
            $dEntropy += ($state->entropy - 0.85) * 0.1;
        }

        // dOrder: grows from cohesion, decays from entropy and trauma
        $dOrder = ($state->cohesion * 0.04)
            - ($state->entropy * 0.03)
            - ($state->trauma * 0.02);

        if ($state->entropy > 0.7) {
            $dOrder -= ($state->entropy - 0.7) * 0.05;
        }

        // dInnovation: grows with order in stable conditions
        $dInnovation = ($state->order * 0.03) * (1.0 - $state->entropy)
            - ($state->inequality * 0.02)
            + ($law->techAccumulationRate * 0.01);

        // Cognitive ceiling caps innovation growth
        if ($state->innovation > $law->cognitiveCeiling) {
            $dInnovation -= ($state->innovation - $law->cognitiveCeiling) * 0.05;
        }

        // dCohesion: grows with order, decays from inequality and trauma
        $dCohesion = ($state->order * 0.02)
            - ($state->inequality * 0.04)
            - ($state->trauma * 0.03);

        $dCohesion += $law->metaSystemAwareness * 0.005;

        // dInequality: innovation creates inequality, cohesion reduces it
        $dInequality = ($state->innovation * 0.02)
            - ($state->cohesion * 0.03)
            + ($state->entropy * 0.01);

        if ($state->order > 0.7 && $law->metaSystemAwareness < 0.3) {
            $dInequality += 0.01;
        }

        // dTrauma: collapse pressure minus healing
        $collapsePressure = $this->calculateCollapsePressure($state, $law);
        $dTrauma = $collapsePressure
            - ($state->cohesion * 0.02)
            - ($state->order * 0.01);

        if ($state->entropy < 0.3 && $state->order > 0.5) {
            $dTrauma -= 0.005;
        }

        return $state->withDelta([
            'entropy' => $dEntropy,
            'order' => $dOrder,
            'innovation' => $dInnovation,
            'cohesion' => $dCohesion,
            'inequality' => $dInequality,
            'trauma' => $dTrauma,
        ]);
    }

    /**
     * Calculate collapse pressure — contributes to trauma growth.
     * CPI formula: base + interaction + elite_break + war_amp
     */
    private function calculateCollapsePressure(WorldStateVector $state, LawVector $law): float
    {
        $pressure = 0.0;

        if ($state->entropy > 0.6) {
            $pressure += ($state->entropy - 0.6) * 0.05;
        }

        if ($state->inequality > 0.7) {
            $pressure += ($state->inequality - 0.7) * 0.08;
        }

        $interaction = $state->entropy * $state->inequality * (1.0 - $state->order);
        $pressure += $interaction * 0.03;

        $pressure += $law->collapseProbability * 0.01;

        return $pressure;
    }
}
