<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\Mathematics;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;

/**
 * Named stress components for narrative and analysis.
 * Maps PressureAccumulationField components to Economic, Political, Cultural, Structural.
 */
class StressModel
{
    public function __construct(
        private readonly PressureAccumulationField $pressureField
    ) {
    }

    public function economicStress(WorldStateVector $s): float
    {
        $resource = $this->pressureField->resourceStress($s);
        $power = $this->pressureField->powerImbalance($s);
        return min(1.0, $resource * 0.6 + $power * 0.4);
    }

    public function politicalStress(WorldStateVector $s): float
    {
        return $this->pressureField->powerImbalance($s);
    }

    public function culturalStress(WorldStateVector $s): float
    {
        return $this->pressureField->ideologyDivergence($s);
    }

    public function structuralEntropy(WorldStateVector $s): float
    {
        return $this->pressureField->socialFragmentation($s);
    }

    /**
     * Optional: military tension (0..1).
     */
    public function militaryTension(WorldStateVector $s): float
    {
        return $s->getMilitary();
    }

    /**
     * All components for narrative / dashboard.
     *
     * @return array{economic_stress: float, political_stress: float, cultural_stress: float, structural_entropy: float, military_tension: float}
     */
    public function getComponents(WorldStateVector $s): array
    {
        return [
            'economic_stress' => $this->economicStress($s),
            'political_stress' => $this->politicalStress($s),
            'cultural_stress' => $this->culturalStress($s),
            'structural_entropy' => $this->structuralEntropy($s),
            'military_tension' => $this->militaryTension($s),
        ];
    }

    /**
     * Weighted total (0..1) for optional nonlinear combination.
     */
    public function totalPressure(WorldStateVector $s, array $weights = []): float
    {
        $defaults = [
            'economic_stress' => 0.25,
            'political_stress' => 0.25,
            'cultural_stress' => 0.2,
            'structural_entropy' => 0.2,
            'military_tension' => 0.1,
        ];
        $w = array_merge($defaults, $weights);
        $c = $this->getComponents($s);
        $sum = 0.0;
        foreach ($w as $key => $weight) {
            $sum += ($c[$key] ?? 0.0) * $weight;
        }
        return max(0.0, min(1.0, $sum));
    }
}
