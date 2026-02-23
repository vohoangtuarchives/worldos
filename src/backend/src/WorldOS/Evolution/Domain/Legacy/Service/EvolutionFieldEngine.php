<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;
use WorldOS\Evolution\Domain\Legacy\ValueObject\CosmicState;
use WorldOS\Evolution\Domain\Legacy\ValueObject\EnvironmentState;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\PhysicalLaws;
use WorldOS\Evolution\Domain\Legacy\Enum\CivilizationPhase;

/**
 * EvolutionFieldEngine
 * 
 * Động cơ hợp nhất các chiều của văn minh dựa trên toán học "Trường" (Field-First) của V3.
 * Thay thế logic tiến hóa rời rạc bằng hệ phương trình vi phân có tính đối trọng.
 */
class EvolutionFieldEngine
{
    private const DT = 0.01;

    public function computeNetForces(
        CivilizationSnapshot $civ,
        CosmicState $cosmic,
        EnvironmentState $env,
        PhysicalLaws $laws,
        CivilizationPhase $phase = CivilizationPhase::STABILITY,
        array $phaseForces = [],
        float $totalFactionPower = 1.0
    ): array {
        // Unpack V4 metrics
        $ce = $civ->culturalEnergy;
        $sc = $civ->spiritualCohesion;
        $tech = $civ->technologicalLevel;
        $stab = $civ->stability;
        $p = $civ->prosperity;
        $mp = $civ->militaryPressure;
        $ie = $civ->internalEntropy;
        $legit = $civ->legitimacy;
        $ecohesion = $civ->eliteCohesion;
        $ineq = $civ->inequality;
        $trauma = $civ->getResidual()->warTrauma;

        // --- BẢN SAO TOÁN HỌC HYPER-D 17-DIMENSION ---
        $sus = $civ->sustainability;
        $myst = $civ->mystery;
        $legacy = $civ->historicalLegacy;
        $exp = $civ->expansionism;
        $info = $civ->informationFlow;
        $mob = $civ->socialMobility;
        $curv = $civ->fieldCurvature;
        
        // 1. Cohesion & Inequality
        $dCohesion = -0.02 * $ineq * (1.0 - $sc) + 0.01 * $mob;

        // 2. Legitimacy & Tech
        $dLegitimacy = -0.01 * $tech * $legit + 0.005 * $cosmic->order + 0.005 * $legacy;

        // 3. Elite Cohesion & Trauma
        $dEliteCohesion = -0.03 * $trauma * $ecohesion + 0.01 * $p;

        // 4. Stability
        $dStability = 0.01 * (1.1 - $cosmic->entropy) - 0.02 * $cosmic->entropy * $stab + 0.005 * $legacy;

        // 5. Internal Entropy (Sustainability impact)
        $dInternalEntropy = 0.005 * (1.0 - $ie) + 0.01 * $ineq - 0.01 * $sc * $ie - 0.005 * $stab + 0.02 * (1.0 - $sus);

        // 6. Tech (Information impact)
        $dTech = 0.01 * $ce * $stab + 0.01 * $info - 0.05 * $ie * $tech * (1.2 - $stab);

        // 7. Military (Expansion impact)
        $dMilitary = 0.005 * $trauma + 0.01 * $exp - 0.01 * $legit;

        // 8. Prosperity (Sustainability impact)
        $dProsperity = 0.02 * $tech * $stab + 0.01 * $sus - 0.005 * $mp - 0.02 * $ie;

        // 9. Inequality (Mobility impact)
        $dInequality = 0.02 * (1.1 - $legit) - 0.01 * $sc * $ineq - 0.01 * $mob;

        // 10. Cultural Energy
        $dCulturalEnergy = 0.01 * $stab - 0.01 * $ie * $ce + 0.005 * $myst;

        // 11-17. NEW HYPER-D DIMENSIONS
        $dSustainability = -0.02 * $tech * $p + 0.01 * $ce - 0.01 * $exp;
        $dMystery = 0.02 * $curv + 0.005 * $sc - 0.01 * $info; // Science kills mystery
        $dLegacy = 0.002 * $ce * $stab; // Heritage builds in peace
        $dExpansion = 0.02 * $p * $mp - 0.01 * $stab; // Greed grows in power, slows in order
        $dInformation = 0.03 * $tech - 0.01 * $ie; // Tech spreads info
        $dSocialMobility = $dInformation * 0.5 - 0.02 * $ineq; // Info helps, inequality blocks

        // --- PHASE FEEDBACK ---
        $dCulturalEnergy += $phaseForces['ce'] ?? 0;
        $dCohesion += $phaseForces['sc'] ?? 0;
        $dTech += $phaseForces['tech'] ?? 0;
        $dStability += $phaseForces['stability'] ?? 0;
        $dProsperity += $phaseForces['prosperity'] ?? 0;
        $dMilitary += $phaseForces['mp'] ?? 0;
        $dInternalEntropy += $phaseForces['ie'] ?? 0;
        $dLegitimacy += $phaseForces['legitimacy'] ?? 0;

        // --- ENERGY LEDGER ---
        $movementCost = (abs($dCulturalEnergy) + abs($dCohesion) + abs($dTech) + abs($dStability) + 
                         abs($dProsperity) + abs($dMilitary) + abs($dInternalEntropy) + abs($dLegitimacy) +
                         abs($dSustainability) + abs($dMystery) + abs($dLegacy) + abs($dExpansion) + 
                         abs($dInformation) + abs($dSocialMobility)) * 4.0;
        
        $maintenanceCost = $ie * 0.05 + (1.0 - $sus) * 0.05;
        $totalEnergyDrain = ($movementCost + $maintenanceCost) * self::DT;

        $damping = 0.4; // Higher dimensionality needs more damping for stability

        return [
            'ce' => $dCulturalEnergy * self::DT * $damping,
            'sc' => $dCohesion * self::DT * $damping,
            'tech' => $dTech * self::DT * $damping,
            'stability' => $dStability * self::DT * $damping,
            'prosperity' => $dProsperity * self::DT * $damping,
            'mp' => $dMilitary * self::DT * $damping,
            'ie' => $dInternalEntropy * self::DT * $damping,
            'legitimacy' => $dLegitimacy * self::DT * $damping,
            'eliteCohesion' => $dEliteCohesion * self::DT * $damping,
            'inequality' => $dInequality * self::DT * $damping,
            'sustainability' => $dSustainability * self::DT * $damping,
            'mystery' => $dMystery * self::DT * $damping,
            'legacy' => $dLegacy * self::DT * $damping,
            'expansion' => $dExpansion * self::DT * $damping,
            'info' => $dInformation * self::DT * $damping,
            'mobility' => $dSocialMobility * self::DT * $damping,
            'energy_drain' => $totalEnergyDrain,
        ];
    }
}
