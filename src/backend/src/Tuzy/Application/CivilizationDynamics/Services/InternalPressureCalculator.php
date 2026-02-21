<?php

namespace Tuzy\Application\CivilizationDynamics\Services;

use Tuzy\Application\CivilizationDynamics\Entities\CivilizationState;
use Tuzy\Application\WorldEvolution\Entities\WorldState;

/**
 * Tính toán áp lực nội tại của một nền văn minh.
 * Áp lực là tổng hợp của Trạng thái hiện tại (State) + Ký ức quá khứ (Residual).
 * Khi áp lực vượt ngưỡng, nó sẽ crack (nứt vỡ) và tạo ra Event.
 */
class InternalPressureCalculator
{
    private const PRESSURE_DIMENSIONS = [
        'social_instability',
        'metaphysical_tension',
        'economic_collapse',
        'ideological_schism'
    ];

    public function calculatePressure(CivilizationState $civ, WorldState $world): array
    {
        $pressures = [];

        // 1. Social Instability Pressure
        // = (Inequality) + (War Trauma) + (Entropy của Thế giới)
        $social = $civ->vector->getInequality() * 0.4
                + $civ->residualMemory->getIntensity('war_trauma') * 0.4
                + $world->vector->getEntropy() * 0.2;
        
        $pressures['social_instability'] = min(1.0, $social);

        // 2. Metaphysical Tension
        // = (Epistemic Instability) + (Metaphysical Scar)
        $meta = $world->epistemicIndex->instability * 0.6
              + $civ->residualMemory->getIntensity('metaphysical_scar') * 0.4;
        
        $pressures['metaphysical_tension'] = min(1.0, $meta);

        // 3. Ideological Schism
        // = (low Elite Cohesion) + (high Disparity/Fog)
        $ideological = (1.0 - $civ->vector->getEliteCohesion()) * 0.5
                     + (1.0 - $world->epistemicIndex->clarity) * 0.5;
        
        $pressures['ideological_schism'] = min(1.0, $ideological);

        return $pressures;
    }
}
