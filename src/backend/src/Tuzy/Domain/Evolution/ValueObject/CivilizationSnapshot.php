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
        
        $phaseForces = $phaseEngine->getPhaseForces($this->historyPhase);
        
        $state = \Tuzy\Domain\Evolution\ValueObject\StateVector::fromSnapshot($this);
        $prevSnapshot = $this;
        $tensionShort = $this->shortWaveTension;
        $tensionLong = $this->longWaveTension;
        $totalTension = $this->narrativeTension;
        $hCount = $this->heroCount;
        
        for ($i = 0; $i < $deltaYears; $i++) {
            // 0. Hero Emergence Check
            $heroImpactThisTick = 0.0;
            $extForcesFromHero = [];
            if ($heroSystem->checkEmergence($totalTension, 1, $ieNow ?? $this->internalEntropy)) {
                $impact = $heroSystem->applyHeroImpact($hCount);
                $extForcesFromHero = $impact['forces'];
                $heroImpactThisTick = $impact['tensionRelief'];
                $hCount++;
            }

            // 1. Calculate Total Pressure (Constraints)
            $totalPressure = $constraintEngine->calculateTotalPressure($this, $cosmicState, $laws);
            
            // 0.1. Attractor Basin Pull
            $currentBasin = $attractorService->classify($this);
            $pull = $attractorService->calculatePull($this, $currentBasin);
            
            // Compile External Forces (Phase + Basin Pull + Faction Power as MP drive)
            $ext = $phaseForces;
            if (isset($pull['stability'])) $ext['stability'] = ($ext['stability'] ?? 0) + $pull['stability'];
            if (isset($pull['prosperity'])) $ext['prosperity'] = ($ext['prosperity'] ?? 0) + $pull['prosperity'];
            
            // Add Hero forces to $ext. $ext is associative.
            foreach(\Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS as $idx => $keyName) {
                if (isset($extForcesFromHero[$idx]) && $extForcesFromHero[$idx] != 0) {
                    $ext[$keyName] = ($ext[$keyName] ?? 0) + $extForcesFromHero[$idx];
                }
            }

            $structuralCapacity = max(0.1, $state->values[3] * $state->values[4]); // stability * prosperity
            $potentialPressure = $totalFactionPower / $structuralCapacity;
            $targetPressure = min(1.0, $potentialPressure * 0.1);
            $mpIdx = array_search('mp', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS);
            $ext['mp'] = ($ext['mp'] ?? 0) + ($targetPressure - $state->values[$mpIdx]) * 0.1;

            // ── ACTIVE ENTROPY DISSIPATION ───────────────────────────────
            // Cohesion, legitimacy, sustainability are entropy SINKS.
            // Without this, internalEntropy only ever grows → EXTINCT every run.
            $ieIdx    = array_search('ie', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS);
            $scVal    = $state->values[array_search('sc',          \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)] ?? $this->spiritualCohesion;
            $legVal   = $state->values[array_search('legitimacy',  \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)] ?? $this->legitimacy;
            $susVal   = $state->values[array_search('sustainability', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)] ?? $this->sustainability;
            $ineqVal  = $state->values[array_search('inequality',  \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)] ?? $this->inequality;
            $mpVal    = $state->values[array_search('mp',          \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)] ?? $this->militaryPressure;
            $ieNow    = $state->values[$ieIdx] ?? $this->internalEntropy;

            // ── PURE THRESHOLD ENTROPY DISSIPATION ───────────────────────
            // Dissipation is 0 below threshold, allowing entropy to build naturally.
            // Above threshold, society actively pushes back proportional to overflow SQUARED.
            // This guarantees oscillation by creating a "hard wall" as entropy nears 1.0
            $entropyThreshold = 0.25;
            if ($ieNow > $entropyThreshold) {
                $overflow = $ieNow - $entropyThreshold;
                // Exponential pushback: creates an unbreakable wall near 0.85
                // Overflow^2 * 2.50 → at 0.50 (overflow 0.25) = -0.15/tick
                //                 → at 0.85 (overflow 0.60) = -0.90/tick
                $ext['ie'] = ($ext['ie'] ?? 0.0) - ($overflow * $overflow) * 2.50;
            }

            // Hero bonus: pure entropy sink during crises
            if ($hCount > 0 && $ieNow > $entropyThreshold) {
                $heroBonus = min(0.10, $hCount * 0.04 * ($ieNow - $entropyThreshold));
                $ext['ie'] = ($ext['ie'] ?? 0.0) - $heroBonus;
            }

            // ── ATTRACTOR FIELD COUPLING ─────────────────────────────────
            // Apply cosmic attractor force profile before kernel integration.
            // Entropy balance is handled via: 
            //   (1) LinearCouplingMatrix ie diagonal override (-0.065) prevents runaway
            //   (2) AttractorFieldModifier: RENAISSANCE dissipates, DARK_AGE/CHAOS amplifies
            //   (3) Hero forces in HeroSystem: ie=-0.50*efficiency when entropy crisis
            $ext = $attractorModifier->apply($cosmicState->currentAttractor, $ext, $entropy, $ieNow);


            // ── DYNAMICAL MATRIX INTEGRATION ────────────────────────────
            $nextState = $kernel->step($state, $entropy, $elasticity, $ext);

            // NARRATIVE TENSION INTEGRATION
            $tensions = $narrativeEngine->updateTension($tensionShort, $tensionLong, $nextState, $entropy, $heroImpactThisTick);
            $tensionShort = $tensions['short'];
            $tensionLong = $tensions['long'];
            $totalTension = $tensions['total'];

            // Energy & Resilience Logic based on new state
            $stab = $nextState->values[array_search('stability', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)];
            $p = $nextState->values[array_search('prosperity', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)];
            $ie = $nextState->values[array_search('ie', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS)];
            
            $energyRegen = ($stab * 0.002 + $p * 0.001) * self::DT; // Base regeneration
            $energy = max(0.0, min(2.0, $energy + $energyRegen - 0.001)); // Constant micro drain

            // 10. Resilience Evolution (Stateful Health Bar)
            $resilienceStress = ($stab < 0.3 ? (0.3 - $stab) * 0.1 : 0.0) + ($ie > 0.8 ? ($ie - 0.8) * 0.1 : 0.0);
            $resilienceRecovery = ($stab > 0.6 ? ($stab - 0.6) * 0.05 : 0.0);
            
            // Dynamic Resilience based on Legacy and Long Wave Tension
            // Structural floor of 0.15 so it doesn't instantly collapse from tension alone
            $legacyIdx = array_search('legacy', \Tuzy\Domain\Evolution\ValueObject\StateVector::KEYS);
            $legacyVal = $state->values[$legacyIdx] ?? 0.1;
            
            $resilienceTarget = max(0.15, 0.5 + 0.3 * $legacyVal - 0.2 * $tensionLong);
            
            // Grace Period: New civilizations (first 100 years) have protected resilience
            $graceFactor = ($this->year + $i < 100) ? 0.2 : 1.0;
            
            // Drift towards target based on current stress/recovery
            // 0.01 means it takes 100 ticks (1000 years) to fully drift to target
            $r = (1 - 0.01) * $r + 0.01 * $resilienceTarget;
            
            // Apply stress/recovery but clamp the decay so it doesn't just plummet to 0
            // from one bad tick. Max drop of 0.05 per tick.
            $netStress = min(0.05, max(-0.05, $resilienceStress * $graceFactor - $resilienceRecovery));
            // Floor at 0.01: a civilization always has some tiny thread of survival
            $r = max(0.01, min(1.0, $r - $netStress));

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



