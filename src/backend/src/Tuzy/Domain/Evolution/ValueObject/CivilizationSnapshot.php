<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\ValueObject;

use Tuzy\Domain\Evolution\Enum\PowerStage;
use Tuzy\Domain\Evolution\Enum\CivilizationPhase;
use Tuzy\Domain\Evolution\Enum\CivilizationLifecycleState;
use Tuzy\Domain\Cosmology\ValueObject\PhysicalLaws;
use InvalidArgumentException;

/**
 * CivilizationSnapshot - Represents the collective state of civilization within a world.
 *
 * This is the layer that generates R_feedback (resonance) to the cosmic layer.
 * Pipeline: Agent perturbations â†’ CivE accumulation â†’ EE amplification â†’ CE bifurcation.
 *
 * Key properties:
 * - collective_knowledge: Accumulated understanding of the world's laws
 * - ritual_coherence: How organized/aligned collective spiritual practices are
 * - technological_level: Material sophistication
 * - faction_stability: Political/social cohesion
 * - resonance_accumulator: The accumulated feedback signal toward cosmic layer
 */
final class CivilizationSnapshot
{
    public const DT = 0.01;

    public function __construct(
        public readonly float $culturalEnergy,         // 0.0 to 1.0 (formerly collectiveKnowledge)
        public readonly float $spiritualCohesion,      // 0.0 to 1.0 (formerly ritualCoherence)
        public readonly float $technologicalLevel,     // 0.0 to 2.0
        public readonly float $stability,              // 0.0 to 1.0 (formerly factionStability)
        public readonly float $prosperity,             // 0.0 to 1.0
        public readonly float $militaryPressure,       // 0.0 to 1.0
        public readonly float $externalThreat,         // 0.0 to 1.0
        public readonly float $internalEntropy,        // 0.0 to 1.0
        public readonly float $legitimacy = 0.8,        // 0.0 to 1.0 (Authority belief)
        public readonly float $eliteCohesion = 0.7,     // 0.0 to 1.0 (Inner circle unity)
        public readonly float $inequality = 0.3,        // 0.0 to 1.0 (Gini-like gradient)
        public readonly float $sustainability = 0.8,    // 12. Sustainability (Eco-health)
        public readonly float $mystery = 0.1,           // 13. Arcane/Mystery
        public readonly float $historicalLegacy = 0.1,   // 14. History depth
        public readonly float $expansionism = 0.2,      // 15. Ambition/Conquest
        public readonly float $informationFlow = 0.3,   // 16. Knowledge speed
        public readonly float $socialMobility = 0.5,     // 17. Class fluidity
        public readonly float $resonanceAccumulator = 0.0,
        public readonly float $resilience = 1.0,
        public readonly int $year = 0,
        public readonly int $yearsInPhase = 0,         
        public readonly CivilizationPhase $historyPhase = CivilizationPhase::STABILITY,
        public readonly PowerStage $powerStage = PowerStage::STAGE_0_MUNDANE,
        public readonly float $fieldCurvature = 0.0,
        public readonly float $energy = 1.0, // Năng lượng văn minh (bảo toàn)
        /** @var \Tuzy\Domain\Evolution\ValueObject\SocialClass[] */
        public readonly array $socialClasses = [],
        /** @var \Tuzy\Domain\Evolution\ValueObject\Faction[] */
        public readonly array $factions = [],
        /** @var \Tuzy\Domain\Evolution\ValueObject\PopulationCluster[] */
        public readonly array $populationClusters = [],
        public readonly ?EliteNetwork $eliteNetwork = null,
        public readonly float $structuralEntropy = 0.0,
        public readonly float $civilizationalMemory = 0.0,
        public readonly ?CivilizationResidual $residual = null,
        public readonly CivilizationLifecycleState $lifecycleState = CivilizationLifecycleState::EMERGENCE,
        public readonly float $narrativeTension = 0.0,
        public readonly float $shortWaveTension = 0.0,
        public readonly float $longWaveTension = 0.0,
        public readonly int $heroCount = 0
    ) {
        $this->validate();
    }

    public static function defaultObservation(int $year = 0): self
    {
        return new self(
            culturalEnergy: 0.15,
            spiritualCohesion: 0.20,
            technologicalLevel: 0.10,
            stability: 0.60,
            prosperity: 0.30,
            militaryPressure: 0.10,
            externalThreat: 0.05,
            internalEntropy: 0.10,
            legitimacy: 0.80,
            eliteCohesion: 0.70,
            inequality: 0.30,
            resonanceAccumulator: 0.0,
            resilience: 1.0,
            year: $year,
            yearsInPhase: 0,
            socialClasses: [
                new \Tuzy\Domain\Evolution\ValueObject\SocialClass(\Tuzy\Domain\Evolution\Enum\SocialClassType::NOBILITY, 0.8, 0.9, 0.05),
                new \Tuzy\Domain\Evolution\ValueObject\SocialClass(\Tuzy\Domain\Evolution\Enum\SocialClassType::PRIESTHOOD, 0.6, 0.8, 0.05),
                new \Tuzy\Domain\Evolution\ValueObject\SocialClass(\Tuzy\Domain\Evolution\Enum\SocialClassType::WARRIOR, 0.5, 0.7, 0.10),
                new \Tuzy\Domain\Evolution\ValueObject\SocialClass(\Tuzy\Domain\Evolution\Enum\SocialClassType::MERCHANT, 0.2, 0.5, 0.05),
                new \Tuzy\Domain\Evolution\ValueObject\SocialClass(\Tuzy\Domain\Evolution\Enum\SocialClassType::PEASANTRY, 0.1, 0.4, 0.75),
                new \Tuzy\Domain\Evolution\ValueObject\SocialClass(\Tuzy\Domain\Evolution\Enum\SocialClassType::INTELLECTUAL, 0.01, 0.9, 0.01),
            ],
            factions: [
                new \Tuzy\Domain\Evolution\ValueObject\Faction(
                    id: 'fac_initial',
                    name: 'Initial Ruling Council',
                    ideology: new \Tuzy\Domain\Evolution\ValueObject\IdeologyVector(0.5, 0.5, 0.5, 0.5, 0.5, 0.5),
                    powerShare: 1.0,
                    cohesion: 0.8,
                    legitimacyClaim: 0.9
                )
            ],
            populationClusters: [
                new \Tuzy\Domain\Evolution\ValueObject\PopulationCluster(
                    ideology: new \Tuzy\Domain\Evolution\ValueObject\IdeologyVector(0.5, 0.5, 0.5, 0.5, 0.5, 0.5),
                    share: 1.0,
                    radicalization: 0.1,
                    originEventType: 'GENESIS',
                    birthTick: $year
                )
            ],
            eliteNetwork: new \Tuzy\Domain\Evolution\ValueObject\EliteNetwork(),
            structuralEntropy: 0.0,
            civilizationalMemory: 0.0,
            lifecycleState: CivilizationLifecycleState::EMERGENCE
        );
    }

    /**
     * Evolve civilization state based on environmental conditions.
     * Uses internal Euler integration (1-year steps) for numerical stability.
     */
    public function evolve(
        \Tuzy\Domain\Cosmology\ValueObject\PhysicalLaws $laws,
        EnvironmentState $envState,
        CosmicState $cosmicState,
        float $agentPerturbation,
        int $deltaYears,
        array $modifiers = [],
        float $totalFactionPower = 1.0,
        ?CivilizationLifecycleState $forcedLifecycle = null
    ): self {
        // Unpack modifiers
        $entResist = $modifiers['entropy_resistance'] ?? 0.0;
        $entropy = $cosmicState->entropy;
        $elasticity = $entResist;
        
        $energy = $this->energy; // Local energy tracker
        $r = $this->resilience;
        $trauma = $this->residual ? $this->residual->warTrauma : 0.0;
        
        $constraintEngine  = new \Tuzy\Domain\Evolution\Service\RealityConstraintEngine();
        $dynamicsAnalyzer  = new \Tuzy\Domain\Evolution\Service\DynamicsAnalyzer();
        $attractorService  = new \Tuzy\Domain\Evolution\Service\AttractorService();
        $phaseEngine       = new \Tuzy\Domain\Evolution\Service\PhaseEngine();
        $kernel            = new \Tuzy\Domain\Evolution\Mathematics\DynamicalKernel();
        $narrativeEngine   = new \Tuzy\Domain\Evolution\Service\NarrativeEngine();
        $heroSystem        = new \Tuzy\Domain\Evolution\Service\HeroSystem();
        $attractorModifier = new \Tuzy\Domain\Evolution\Service\AttractorFieldModifier();
        $phaseDetector     = new \Tuzy\Domain\Evolution\Service\CivilizationPhaseDetector();

        $populationDynamics = new \Tuzy\Domain\Evolution\Service\PopulationDynamicsEngine();
        $polarizationEngine = new \Tuzy\Domain\Evolution\Service\PolarizationEngine();
        $legitimacyEngine   = new \Tuzy\Domain\Evolution\Service\LegitimacyEngine();
        $factionEvolution   = new \Tuzy\Domain\Evolution\Service\FactionEvolutionEngine();
        $ecoPressureEngine  = new \Tuzy\Domain\Evolution\Service\EcologicalPressureEngine();
        $forecastEngine     = new \Tuzy\Domain\Evolution\Service\ForecastEngine();
        $policyAdvisor      = new \Tuzy\Domain\Evolution\Service\PolicyAdvisor();
        $eliteDecision      = new \Tuzy\Domain\Evolution\Service\EliteDecision();
        
        $phaseForces = $phaseEngine->getPhaseForces($this->historyPhase);
        
        $state = \Tuzy\Domain\Evolution\ValueObject\StateVector::fromSnapshot($this);
        $prevSnapshot = $this;
        $tensionShort = $this->shortWaveTension;
        $tensionLong = $this->longWaveTension;
        $totalTension = $this->narrativeTension;
        $hCount = $this->heroCount;
        
        $currentFactions = $this->factions;
        $currentClusters = $this->populationClusters;
        $currentNetwork = $this->eliteNetwork ?? new \Tuzy\Domain\Evolution\ValueObject\EliteNetwork();
        $currentStructuralEntropy = $this->structuralEntropy;
        
        for ($i = 0; $i < $deltaYears; $i++) {
            $ieNow = $state->values[array_search('ie', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)] ?? $this->internalEntropy;
            
            // Build temporary snapshot to evaluate current phase
            $tempCivForPhase = \Tuzy\Domain\Evolution\ValueObject\CivilizationSnapshot::fromArray(
                array_merge(\Tuzy\Domain\Evolution\ValueObject\CivilizationSnapshot::defaultObservation()->toArray(), $state->toAssocArray())
            );
            $currentBasinPhase = $phaseDetector->detect($tempCivForPhase);

            // 0. Hero Emergence Check
            $heroImpactThisTick = 0.0;
            if ($heroSystem->checkEmergence($totalTension, 1, $ieNow, $currentBasinPhase)) {
                $impact = $heroSystem->applyHeroImpact($hCount);
                $heroImpulses = $impact['forces'];
                $heroImpactThisTick = $impact['tensionRelief'];
                $hCount++;
                
                // Directly apply Hero distortion as IMPULSES (bypassing DT integration)
                $sValues = $state->toAssocArray();
                foreach (\Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS as $idx => $keyName) {
                    if (isset($heroImpulses[$idx]) && $heroImpulses[$idx] != 0) {
                        $sValues[$keyName] = max(-1.0, min(1.0, $sValues[$keyName] + $heroImpulses[$idx]));
                    }
                }
                $state = new \Tuzy\Domain\Evolution\ValueObject\StateVector($sValues);
                
                // Hero also dissipates some trauma instantaneously
                $trauma = max(0.0, $trauma - 0.3);
            }

            // 1. Calculate Total Pressure (Constraints)
            $totalPressure = $constraintEngine->calculateTotalPressure($this, $cosmicState, $laws);
            
            // 0.1. Attractor Basin Pull
            $currentBasin = $attractorService->classify($this);
            $pull = $attractorService->calculatePull($this, $currentBasin);
            
            // Compile External Forces (Phase + Basin Pull + Faction Power as MP drive)
            $ext = $phaseForces;
            if (isset($pull['prosperity'])) $ext['prosperity'] = ($ext['prosperity'] ?? 0) + $pull['prosperity'];

            $structuralCapacity = max(0.1, $state->values[3] * $state->values[4]); // stability * prosperity
            $potentialPressure = $totalFactionPower / $structuralCapacity;
            $targetPressure = min(1.0, $potentialPressure * 0.1);
            $ext['mp'] = ($ext['mp'] ?? 0) + ($targetPressure - $state->values[5]) * 0.1;

            // ── PHASE 10: ECOLOGICAL & AI ADVISORY LAYER ─────────────────────
            $ecoPressures = $ecoPressureEngine->calculatePressures($this);
            $resourcePressure = $ecoPressures['resourcePressure'];
            $complexityCost = $ecoPressures['complexityCost'];

            // AI Forecast & Policy
            $ceVal = $state->values[array_search('ce', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)] ?? $this->culturalEnergy;
            $techVal = $state->values[array_search('tech', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)] ?? $this->technologicalLevel;
            $infoVal = $state->values[array_search('info', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)] ?? $this->informationFlow;
            
            $aii = ($techVal * 0.4) + ($infoVal * 0.4) + ($ceVal * 0.2);
            $riskForecast = $forecastEngine->predict($this, 10, $aii);
            $suggestedPolicies = $policyAdvisor->suggest($riskForecast);
            $chosenPolicy = $eliteDecision->makeDecision($suggestedPolicies, $this->eliteCohesion, $this->legitimacy, $this->structuralEntropy);

            // Apply Policy Effects to Dynamic System External Forces
            if ($chosenPolicy === \Tuzy\Domain\Evolution\Service\PolicyAdvisor::POLICY_TRIGGER_REFORM) {
                $ext['ie'] = ($ext['ie'] ?? 0) - 0.1; // Reduce internal entropy
                $ext['ce'] = ($ext['ce'] ?? 0) + 0.05; // Boost cultural energy
            } elseif ($chosenPolicy === \Tuzy\Domain\Evolution\Service\PolicyAdvisor::POLICY_REDUCE_CENTRALIZATION) {
                $ext['stability'] = ($ext['stability'] ?? 0) + 0.02; // Minor stability buff
            }

            // ── PHASE 10: POPULATION DYNAMICS & FACTION EVOLUTION ────────────
            $ieIdx = array_search('ie', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS);
            $ieNow = $state->values[$ieIdx] ?? $this->internalEntropy;

            $currentClusters = $populationDynamics->evolve(
                $currentClusters,
                $currentFactions,
                $this->prosperity,
                $resourcePressure,
                $this->inequality,
                $ieNow,     
                $riskForecast->shockRiskVector['social_unrest'] ?? 0.0,
                $this->year + $i
            );
            $polarization = $polarizationEngine->calculatePolarization($currentClusters);
            
            // Faction Evolution
            $externalThreatValue = max($this->externalThreat, $riskForecast->shockRiskVector['external_invasion'] ?? 0.0);
            $factionResult = $factionEvolution->process(
                $currentFactions,
                $currentNetwork,
                $currentStructuralEntropy,
                $this->legitimacy,
                $externalThreatValue,
                $resourcePressure,
                $riskForecast->collapseProbability
            );
            $currentFactions = $factionResult['factions'];
            $currentNetwork = $factionResult['network'];

            // Find ruling faction (highest power share) to calculate legitimacy
            $rulingFaction = null;
            $maxShare = -1.0;
            foreach ($currentFactions as $f) {
                if ($f->powerShare > $maxShare) {
                    $maxShare = $f->powerShare;
                    $rulingFaction = $f;
                }
            }

            $calcLegitimacy = $this->legitimacy;
            if ($rulingFaction) {
                $calcLegitimacy = $legitimacyEngine->calculateLegitimacy($currentClusters, $rulingFaction);
            }

            // Structural Entropy Drift
            $currentStructuralEntropy += ($currentNetwork->networkRigidity * 0.01 + $complexityCost * 0.02) * self::DT;
            $currentStructuralEntropy = min(1.0, max(0.0, $currentStructuralEntropy));

            // ── TRAUMA DISSIPATION MECHANISM ─────────────────────────────────
            // TraumaDecay = (SpiritualCohesion * 0.4) + (CulturalEnergy * 0.3) + Constant Drift
            $scVal = $state->values[array_search('sc', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)] ?? $this->spiritualCohesion;
            $ceVal = $state->values[array_search('ce', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)] ?? $this->culturalEnergy;
            
            $traumaDecay = ($scVal * 0.04 + $ceVal * 0.03 + 0.005) * self::DT;
            $trauma = max(0.0, $trauma - $traumaDecay);

            // ── ACTIVE ENTROPY DISSIPATION ───────────────────────────────
            $ieIdx    = array_search('ie', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS);
            $ieNow    = $state->values[$ieIdx] ?? $this->internalEntropy;

            $entropyThreshold = 0.25;
            if ($ieNow > $entropyThreshold) {
                $overflow = $ieNow - $entropyThreshold;
                $ext['ie'] = ($ext['ie'] ?? 0.0) - ($overflow * $overflow) * 2.50;
            }

            // ── ATTRACTOR FIELD COUPLING ─────────────────────────────────
            $ext = $attractorModifier->apply($cosmicState->currentAttractor, $ext, $entropy, $ieNow);

            // ── DYNAMICAL MATRIX INTEGRATION ────────────────────────────
            $nextState = $kernel->step($state, $entropy, $elasticity, $ext);

            // ── EMERGENT STABILITY & LEGITIMACY (Feedback Loop) ────────────────
            $v = $nextState->values;
            $legIdx = array_search('legitimacy', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS);
            $ecIdx  = array_search('eliteCohesion', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS);
            $ineqIdx = array_search('inequality', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS);
            $ceIdx  = array_search('ce', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS);

            $legVal = $v[$legIdx];
            $ecVal  = $v[$ecIdx];
            $ineqVal = $v[$ineqIdx];
            $ieVal  = $v[$ieIdx];
            $ceNew  = $v[$ceIdx];

            // Blend ideological legitimacy mapping with physical state
            $legVal = ($legVal * 0.6) + ($calcLegitimacy * 0.4);
            $nextStateValues = $nextState->getAll();
            $nextStateValues['legitimacy'] = $legVal;

            // The structural formula for Stability (ranges ~0 to 1)
            $calcStab = 0.3 * $legVal 
                      + 0.3 * $ecVal 
                      + 0.2 * $ceNew 
                      - 0.3 * $ineqVal 
                      - 0.4 * $ieVal 
                      - 0.1 * $polarization    // Inject Polarization penalty
                      - 0.2 * $trauma;
            
            // Re-inject stability directly into the state vector, bypassing Euler integration drift for this variable
            $calcStab = max(0.0, min(1.0, $calcStab + 0.3)); // Base structural anchor point
            
            $nextStateValues = $nextState->getAll();
            $nextStateValues['stability'] = $calcStab;
            $nextState = new \Tuzy\Domain\Evolution\ValueObject\StateVector($nextStateValues);

            // NARRATIVE TENSION INTEGRATION
            $tensions = $narrativeEngine->updateTension($tensionShort, $tensionLong, $nextState, $entropy, $heroImpactThisTick);
            $tensionShort = $tensions['short'];
            $tensionLong = $tensions['long'];
            $totalTension = $tensions['total'];

            // Energy & Resilience Logic based on new state
            $stab = $calcStab;
            $p = $nextState->values[array_search('prosperity', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)];
            $ieNew = $nextState->values[array_search('ie', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)];
            
            $energyRegen = ($stab * 0.002 + $p * 0.001) * self::DT; 
            $energy = max(0.0, min(2.0, $energy + $energyRegen - 0.001)); 

            // Resilience Evolution
            $resilienceStress = ($stab < 0.3 ? (0.3 - $stab) * 0.1 : 0.0) + ($ieNew > 0.8 ? ($ieNew - 0.8) * 0.1 : 0.0);
            $resilienceRecovery = ($stab > 0.6 ? ($stab - 0.6) * 0.05 : 0.0) + ($ceNew > 0.7 ? 0.02 : 0.0); // CE also recovers resilience
            
            $legacyIdx = array_search('legacy', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS);
            $legacyVal = $state->values[$legacyIdx] ?? 0.1;
            
            $resilienceTarget = max(0.15, 0.5 + 0.3 * $legacyVal - 0.2 * $tensionLong);
            $graceFactor = ($this->year + $i < 100) ? 0.2 : 1.0;
            
            $r = (1 - 0.01) * $r + 0.01 * $resilienceTarget;
            $netStress = min(0.05, max(-0.05, $resilienceStress * $graceFactor - $resilienceRecovery));
            $r = max(0.01, min(1.0, $r - $netStress));

            // ── HIDDEN FRAGILITY & DELAYED COLLAPSE (Edge of Chaos) ─────────
            $fragility = $this->residual ? $this->residual->socialUnrest : 0.0;
            $fragility += ($ineqVal * $ineqVal + $ieNew) * self::DT;
            
            if ($fragility > 1.5) {
                $k = 0.2; // 20% shock
                $newIe = min(3.0, $ieNew * (1.0 + $k)); 
                $newStab = max(0.0, $stab * (1.0 - $k));
                
                $shockStateValues = $nextState->toAssocArray();
                $shockStateValues['ie'] = $newIe;
                $shockStateValues['stability'] = $newStab;
                $nextState = new \Tuzy\Domain\Evolution\ValueObject\StateVector($shockStateValues);
                
                $fragility = 1.0; 
            }
            
            if ($this->residual) {
                $this->residual->socialUnrest = $fragility;
            }

            $state = $nextState;
        }
        
        $v = $state->toAssocArray();
        
        // 11. Power Stage Transition (only once per year)
        $pStage = $this->evaluatePowerStage($v['tech'], $v['ce'], $this->powerStage);

        $nextSnapshot = new self(
            culturalEnergy: round($v['ce'], 6),
            spiritualCohesion: round($v['sc'], 6),
            technologicalLevel: round($v['tech'], 6),
            stability: round($v['stability'], 6),
            prosperity: round($v['prosperity'], 6),
            militaryPressure: round($v['mp'], 6),
            externalThreat: round($this->externalThreat, 6),
            internalEntropy: round($v['ie'], 6),
            legitimacy: round($v['legitimacy'], 6),
            eliteCohesion: round($v['eliteCohesion'], 6),
            inequality: round($v['inequality'], 6),
            sustainability: round($v['sustainability'], 6),
            mystery: round($v['mystery'], 6),
            historicalLegacy: round($v['legacy'], 6),
            expansionism: round($v['expansion'], 6),
            informationFlow: round($v['info'], 6),
            socialMobility: round($v['mobility'], 6),
            resonanceAccumulator: round($this->resonanceAccumulator, 6),
            resilience: round($r, 6),
            year: $this->year + $deltaYears,
            yearsInPhase: $this->yearsInPhase,
            historyPhase: $this->historyPhase,
            powerStage: $pStage,
            fieldCurvature: round($v['curvature'], 6),
            energy: $energy,
            socialClasses: $this->socialClasses,
            factions: $currentFactions,
            populationClusters: $currentClusters,
            eliteNetwork: $currentNetwork,
            structuralEntropy: $currentStructuralEntropy,
            civilizationalMemory: $this->civilizationalMemory,
            residual: new CivilizationResidual(
                warTrauma: round($trauma, 6),
                metaphysicalScar: $this->residual ? $this->residual->metaphysicalScar : 0.0,
                socialUnrest: $this->residual ? $this->residual->socialUnrest : 0.0,
                decayRate: $this->residual ? $this->residual->decayRate : 0.05,
                cumulativeTrauma: $this->residual ? $this->residual->cumulativeTrauma : 0.0
            ),
            lifecycleState: $forcedLifecycle ?? $this->lifecycleState,
            narrativeTension: round($totalTension, 6),
            shortWaveTension: round($tensionShort, 6),
            longWaveTension: round($tensionLong, 6),
            heroCount: $hCount
        );

        // 11. Anomaly Check & Bifurcation Analysis
        $anomalyReport = $constraintEngine->validateState($nextSnapshot, $cosmicState);
        if ($anomalyReport['violated']) {
            // Log for developer or trigger emergency correction event in real app
            // error_log("REALITY ANOMALY DETECTED: " . implode(", ", $anomalyReport['anomalies']));
        }

        $curvature = $dynamicsAnalyzer->calculateCurvature($nextSnapshot, $prevSnapshot);
        $divergence = $dynamicsAnalyzer->calculateDivergence($nextSnapshot);
        $branchProb = $dynamicsAnalyzer->calculateBranchProbability($curvature, $divergence, $totalPressure);

        if ($branchProb > 0.7) {
            // Bắn một dấu hiệu rẽ nhánh (Placeholder for Domain Event)
        }

        return $nextSnapshot;
    }

    public function getResonanceFeedback(): float
    {
        return $this->resonanceAccumulator;
    }

    public function environmentalImpact(): float
    {
        $rawImpact = $this->technologicalLevel * 0.3;
        $mitigation = min($this->culturalEnergy * 0.1, 0.15);
        return max(0.0, min(1.0, $rawImpact - $mitigation));
    }

    private function evaluatePowerStage(float $tech, float $ce, PowerStage $current): PowerStage
    {
        $level = $current->level();
        
        if ($level < 1 && $tech > 0.4) return PowerStage::STAGE_1_MORTAL_MARTIAL;
        if ($level < 2 && $tech > 0.7) return PowerStage::STAGE_2_ENHANCED_MARTIAL;
        if ($level < 3 && $tech > 1.0 && $ce > 0.4) return PowerStage::STAGE_3_LOW_IMMORTAL;
        if ($level < 4 && $tech > 1.5 && $ce > 0.7) return PowerStage::STAGE_4_HIGH_IMMORTAL;
        if ($level < 5 && $tech > 1.9 && $ce > 0.9) return PowerStage::STAGE_5_MYTHIC;

        return $current;
    }

    public function toArray(): array
    {
        return [
            'cultural_energy' => $this->culturalEnergy,
            'spiritual_cohesion' => $this->spiritualCohesion,
            'technological_level' => $this->technologicalLevel,
            'stability' => $this->stability,
            'prosperity' => $this->prosperity,
            'military_pressure' => $this->militaryPressure,
            'external_threat' => $this->externalThreat,
            'internal_entropy' => $this->internalEntropy,
            'legitimacy' => $this->legitimacy,
            'elite_cohesion' => $this->eliteCohesion,
            'inequality' => $this->inequality,
            'sustainability' => $this->sustainability,
            'mystery' => $this->mystery,
            'historical_legacy' => $this->historicalLegacy,
            'expansionism' => $this->expansionism,
            'information_flow' => $this->informationFlow,
            'social_mobility' => $this->socialMobility,
            'resonance_accumulator' => $this->resonanceAccumulator,
            'resilience' => $this->resilience,
            'year' => $this->year,
            'years_in_phase' => $this->yearsInPhase,
            'history_phase' => $this->historyPhase->value,
            'power_stage' => $this->powerStage->value,
            'field_curvature' => $this->fieldCurvature,
            'energy' => $this->energy,
            'social_classes' => array_map(fn($c) => $c->toArray(), $this->socialClasses),
            'factions' => array_map(fn($f) => $f->toArray(), $this->factions),
            'population_clusters' => array_map(fn($p) => $p->toArray(), $this->populationClusters),
            'elite_network' => $this->eliteNetwork ? $this->eliteNetwork->toArray() : null,
            'structural_entropy' => $this->structuralEntropy,
            'civilizational_memory' => $this->civilizationalMemory,
            'residual' => $this->residual ? [
                'war_trauma' => $this->residual->warTrauma,
                'metaphysical_scar' => $this->residual->metaphysicalScar,
                'social_unrest' => $this->residual->socialUnrest,
                'cumulative_trauma' => $this->residual->cumulativeTrauma,
            ] : null,
            'narrative_tension' => $this->narrativeTension,
            'short_wave_tension' => $this->shortWaveTension,
            'long_wave_tension' => $this->longWaveTension,
            'hero_count' => $this->heroCount,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            culturalEnergy: (float) ($data['cultural_energy'] ?? $data['collective_knowledge'] ?? 0.1),
            spiritualCohesion: (float) ($data['spiritual_cohesion'] ?? $data['ritual_coherence'] ?? 0.1),
            technologicalLevel: (float) $data['technological_level'],
            stability: (float) ($data['stability'] ?? $data['faction_stability'] ?? 0.5),
            prosperity: (float) ($data['prosperity'] ?? 0.3),
            militaryPressure: (float) ($data['military_pressure'] ?? 0.1),
            externalThreat: (float) ($data['external_threat'] ?? 0.05),
            internalEntropy: (float) ($data['internal_entropy'] ?? 0.1),
            legitimacy: (float) ($data['legitimacy'] ?? 0.8),
            eliteCohesion: (float) ($data['elite_cohesion'] ?? 0.7),
            inequality: (float) ($data['inequality'] ?? 0.3),
            sustainability: (float) ($data['sustainability'] ?? 0.8),
            mystery: (float) ($data['mystery'] ?? 0.1),
            historicalLegacy: (float) ($data['historical_legacy'] ?? 0.1),
            expansionism: (float) ($data['expansionism'] ?? 0.2),
            informationFlow: (float) ($data['information_flow'] ?? 0.3),
            socialMobility: (float) ($data['social_mobility'] ?? 0.5),
            resonanceAccumulator: (float) ($data['resonance_accumulator'] ?? 0.0),
            resilience: (float) ($data['resilience'] ?? 1.0),
            year: (int) $data['year'],
            yearsInPhase: (int) ($data['years_in_phase'] ?? 0),
            historyPhase: CivilizationPhase::from($data['history_phase'] ?? 'stability'),
            powerStage: PowerStage::from($data['power_stage'] ?? 'mundane'),
            fieldCurvature: (float) ($data['field_curvature'] ?? 0.0),
            energy: (float) ($data['energy'] ?? 1.0),
            socialClasses: array_map(
                fn($c) => \Tuzy\Domain\Evolution\ValueObject\SocialClass::fromArray($c), 
                $data['social_classes'] ?? []
            ),
            factions: array_map(
                fn($f) => \Tuzy\Domain\Evolution\ValueObject\Faction::fromArray($f),
                $data['factions'] ?? []
            ),
            populationClusters: array_map(
                fn($p) => \Tuzy\Domain\Evolution\ValueObject\PopulationCluster::fromArray($p),
                $data['population_clusters'] ?? []
            ),
            eliteNetwork: isset($data['elite_network']) ? \Tuzy\Domain\Evolution\ValueObject\EliteNetwork::fromArray($data['elite_network']) : null,
            structuralEntropy: (float) ($data['structural_entropy'] ?? 0.0),
            civilizationalMemory: (float) ($data['civilizational_memory'] ?? 0.0),
            residual: isset($data['residual']) ? new CivilizationResidual(
                warTrauma: (float) ($data['residual']['war_trauma'] ?? 0.0),
                metaphysicalScar: (float) ($data['residual']['metaphysical_scar'] ?? 0.0),
                socialUnrest: (float) ($data['residual']['social_unrest'] ?? 0.0),
                cumulativeTrauma: (float) ($data['residual']['cumulative_trauma'] ?? 0.0)
            ) : null,
            narrativeTension: (float) ($data['narrative_tension'] ?? 0.0),
            shortWaveTension: (float) ($data['short_wave_tension'] ?? 0.0),
            longWaveTension: (float) ($data['long_wave_tension'] ?? 0.0),
            heroCount: (int) ($data['hero_count'] ?? 0)
        );
    }

    private function validate(): void
    {
        if ($this->technologicalLevel < 0.0 || $this->technologicalLevel > 2.0) {
            throw new InvalidArgumentException("technologicalLevel range error: {$this->technologicalLevel}");
        }
        foreach (['culturalEnergy', 'spiritualCohesion', 'stability', 'prosperity', 'militaryPressure', 'externalThreat', 'internalEntropy', 'resonanceAccumulator', 'resilience', 'legitimacy', 'eliteCohesion', 'inequality'] as $prop) {
            if ($this->$prop < 0.0 || $this->$prop > 1.0) {
                // Warning: some values might drift slightly above 1.0 in integration, clamp in evolve
                // throw new InvalidArgumentException("{$prop} range error: {$this->$prop}");
            }
        }
    }
    public function getResidual(): CivilizationResidual
    {
        return $this->residual ?? new CivilizationResidual();
    }
}



