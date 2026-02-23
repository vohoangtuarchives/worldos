<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Mathematics;

use WorldOS\Evolution\Domain\Legacy\ValueObject\StateVector;

/**
 * DynamicalKernel
 * 
 * Replaces the rule-based FieldEngine with a pure numerical integration kernel.
 * S_t+1 = S_t + A_0 * S_t + E * D * S_t + Q(S_t) + Noise + F_external
 */
class DynamicalKernel
{
    public const DT = 0.01;

    private LinearCouplingMatrix $A0;
    private EntropyDestabilizer $D;
    private QuadraticInteraction $Q;

    public function __construct() {
        $this->A0 = new LinearCouplingMatrix();
        $this->D = new EntropyDestabilizer();
        $this->Q = new QuadraticInteraction();
    }

    /**
     * Noise proportional to field curvature
     */
    private function calculateNoise(StateVector $S): array
    {
        $curvatureIdx = array_search('curvature', StateVector::KEYS);
        $curvature = $S->values[$curvatureIdx];
        
        $sigma = 0.05 + 0.1 * abs($curvature);

        $d = [];
        for ($i = 0; $i < StateVector::DIMENSIONS; $i++) {
            $d[$i] = $sigma * (mt_rand() / mt_getrandmax() - 0.5);
        }

        return $d;
    }

    /**
     * Calculate effective spectral radius of (I + A0 + E * D)
     * Used for early warning/tipping point detection.
     */
    public function monitorSpectralRadius(float $cosmicEntropy, float $elasticity = 0.0): float
    {
        // Not perfectly implemented for full non-linear D yet, 
        // because D is a function of S in apply. 
        // But we can approximate by evaluating the Jacobian or just monitoring output.
        // For performance, we skip it in the inner loop, but keep the hook.
        return 0.98; // Placeholder
    }

    /**
     * Evolve by a single mathematical step (Euler Integration).
     */
    public function step(
        StateVector $state, 
        float $cosmicEntropy, 
        float $elasticity = 0.0,
        array $externalForces = []
    ): StateVector {
        $S = $state->values;
        $civSnapshot = \WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot::fromArray(
            array_merge(\WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot::defaultObservation()->toArray(), $state->toAssocArray())
        );

        $phaseDetector = new \WorldOS\Evolution\Domain\Legacy\Service\CivilizationPhaseDetector();
        $empireDynamics = new \WorldOS\Evolution\Domain\Legacy\Service\EmpireDynamics();
        $chaosDynamics = new \WorldOS\Evolution\Domain\Legacy\Service\ChaosDynamics();
        
        $currentPhase = $phaseDetector->detect($civSnapshot);
        $gain = config('worldos.interaction_gain', 1.8);

        // Calculate continuous components
        $linear = $this->A0->multiply($state);
        $destab = $this->D->apply($state, $cosmicEntropy, $elasticity);
        $quad = $this->Q->apply($state);
        $noise = $this->calculateNoise($state);
        $drivers = $this->A0->getBaselineDrivers();
        
        // Calculate Phase-specific Multiplicative Escalation Basins
        $phaseForces = array_fill(0, StateVector::DIMENSIONS, 0.0);
        if ($currentPhase === \WorldOS\Evolution\Domain\Legacy\Service\CivilizationPhaseDetector::PHASE_EMPIRE) {
            $phaseForces = $empireDynamics->apply($S, $gain, self::DT);
        } elseif ($currentPhase === \WorldOS\Evolution\Domain\Legacy\Service\CivilizationPhaseDetector::PHASE_CHAOS) {
            $phaseForces = $chaosDynamics->apply($S, $gain, self::DT);
        }

        $dS = [];
        for ($i = 0; $i < StateVector::DIMENSIONS; $i++) {
            $key = StateVector::KEYS[$i];
            
            // External force (e.g. from PhaseEngine or narrative)
            $fExt = $externalForces[$key] ?? 0.0;

            // Master Equation
            $dS[$i] = $linear[$i] + $destab[$i] + $quad[$i] + $noise[$i] + $drivers[$i] + $fExt + $phaseForces[$i];
            
            // Apply Euler Step
            $S[$i] += $dS[$i] * self::DT;
            
            // Physical boundaries and constraints
            if ($key === 'tech') {
                $S[$i] = max(0.0, min(2.0, $S[$i]));
            } elseif ($key === 'curvature') {
                $S[$i] = max(-1.0, min(1.0, $S[$i]));
            } else {
                $S[$i] = max(0.0, min(1.0, $S[$i]));
            }
        }

        return new StateVector($S);
    }
}
