<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;
use WorldOS\Evolution\Domain\Legacy\ValueObject\CosmicState;
use WorldOS\Evolution\Domain\Legacy\ValueObject\SensitivityMatrix;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\PhysicalLaws;

/**
 * RealityConstraintEngine
 * 
 * Enforces non-linear coupling and physical invariants across the 10 dimensions.
 * Restores the V3 logic: BT + FA + CIT pressure fields.
 */
class RealityConstraintEngine
{
    /**
     * Calculate the non-linear "Reality Tension" (formerly Base Tension + Feedback + Cross-Impact).
     */
    public function calculateTotalPressure(
        CivilizationSnapshot $civ, 
        CosmicState $cosmic, 
        PhysicalLaws $laws
    ): float {
        // 1. Power Imbalance (α): Inequality vs Legitimacy
        $powerImbalance = $civ->inequality * (1.0 - $civ->legitimacy);
        
        // 2. Resource Stress (β): Prosperity vs Population/Need (simplified as inverse prosperity)
        $resourceStress = 1.0 - $civ->prosperity;
        
        // 3. Ideology Divergence (γ): Inverse of spiritual cohesion and elite unity
        $ideologyDivergence = (1.0 - $civ->spiritualCohesion) * 0.7 + (1.0 - $civ->eliteCohesion) * 0.3;
        
        // 4. Social Fragmentation (δ): Stability vs Internal Entropy
        $socialFragmentation = (1.0 - $civ->stability) * 0.6 + $civ->internalEntropy * 0.4;

        // 5. Environmental Pressure (ε): 1.0 - Sustainability
        $environmentalPressure = 1.0 - $civ->sustainability;

        // Base Tension (BT) - Updated for 17D
        $bt = $laws->alpha * $powerImbalance 
            + $laws->beta * $resourceStress 
            + $laws->gamma * $ideologyDivergence 
            + $laws->delta * $socialFragmentation
            + 0.1 * $environmentalPressure; // Environmental debt

        // Feedback Amplification (FA)
        $fa = $laws->feedbackK * ($bt * $bt);

        // Cross-Impact Terms (CIT)
        $cit = ($powerImbalance * $resourceStress) + ($ideologyDivergence * $socialFragmentation) + ($environmentalPressure * $civ->internalEntropy);

        return max(0.0, min(1.0, $bt + $fa + $cit));
    }

    /**
     * Apply constraints to the rate of change (forces).
     * This is the "Sensitivity Matrix" logic.
     */
    public function constrainForces(array $forces, float $totalPressure, PhysicalLaws $laws): array
    {
        // If pressure is extreme, suppress positive growth and amplify decay
        // V4 Hyper-D: Sustainability high can buffer pressure
        $suppressionFactor = 1.0 - ($totalPressure * 0.5);
        $amplificationFactor = 1.0 + ($totalPressure * 1.5); // Decay is much more sensitive to pressure

        foreach ($forces as $key => $value) {
            if ($value > 0) {
                // Diminishing returns on growth as pressure builds
                $forces[$key] *= $suppressionFactor;
            } else {
                // Accelerated decay as pressure builds
                $forces[$key] *= $amplificationFactor;
            }
        }

        return $forces;
    }

    /**
     * Advanced Coupling Matrix: Cross-dimension sensitivity.
     * scaling factor for force on Dim A given current value of Dim B.
     */
    /**
     * Advanced Coupling Matrix: Cross-dimension sensitivity.
     * Uses SensitivityMatrix VO to apply weight-based coupling.
     */
    public function applyCouplingMatrix(array $forces, CivilizationSnapshot $civ): array
    {
        $matrix = SensitivityMatrix::default();
        
        $stateValues = [
            'ce' => $civ->culturalEnergy,
            'sc' => $civ->spiritualCohesion,
            'tech' => $civ->technologicalLevel,
            'stability' => $civ->stability,
            'prosperity' => $civ->prosperity,
            'mp' => $civ->militaryPressure,
            'legit' => $civ->legitimacy,
            'ecohesion' => $civ->eliteCohesion,
            'ineq' => $civ->inequality,
            'ie' => $civ->internalEntropy,
            'sustainability' => $civ->sustainability,
            'mystery' => $civ->mystery,
            'legacy' => $civ->historicalLegacy,
            'expansion' => $civ->expansionism,
            'info' => $civ->informationFlow,
            'mobility' => $civ->socialMobility,
        ];

        return $matrix->apply($forces, $stateValues);
    }

    /**
     * Check for silent physical failures or illogical state combinations.
     * 
     * @return array{violated: bool, anomalies: array}
     */
    public function validateState(CivilizationSnapshot $civ, CosmicState $cosmic): array
    {
        $anomalies = [];

        // 1. Stability-Prosperity Paradox: Prosperity sụp đổ nhưng Stability vẫn cao ngất
        if ($civ->prosperity < 0.1 && $civ->stability > 0.8) {
            $anomalies[] = "Stability-Prosperity Paradox: Extreme poverty without social instability.";
        }

        // 2. Entropy-Cohesion Paradox: Entropy cực cao nhưng xã hội vẫn gắn kết hoàn hảo
        if ($cosmic->entropy > 0.9 && $civ->stability > 0.9) {
            $anomalies[] = "Entropy-Cohesion Paradox: Cosmic chaos without social fragmentation.";
        }

        // 3. Legitimacy-Inequality Paradox: Bất bình đẳng cực cao nhưng chính danh vẫn tuyệt đối
        if ($civ->inequality > 0.8 && $civ->legitimacy > 0.9) {
            $anomalies[] = "Legitimacy-Inequality Paradox: Absolute power belief in extreme stratification.";
        }

        // 4. Tech-Prosperity Leak: Công nghệ cao nhưng thịnh vượng không tăng
        if ($civ->technologicalLevel > 1.5 && $civ->prosperity < 0.2) {
            $anomalies[] = "Innovation Leak: High technology fails to sustain prosperity.";
        }

        return [
            'violated' => !empty($anomalies),
            'anomalies' => $anomalies
        ];
    }
}
