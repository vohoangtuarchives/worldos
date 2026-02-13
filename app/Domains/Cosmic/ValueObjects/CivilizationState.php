<?php

declare(strict_types=1);

namespace App\Domains\Cosmic\ValueObjects;

use InvalidArgumentException;

/**
 * CivilizationState - Represents the collective state of civilization within a world.
 *
 * This is the layer that generates R_feedback (resonance) to the cosmic layer.
 * Pipeline: Agent perturbations → CivE accumulation → EE amplification → CE bifurcation.
 *
 * Key properties:
 * - collective_knowledge: Accumulated understanding of the world's laws
 * - ritual_coherence: How organized/aligned collective spiritual practices are
 * - technological_level: Material sophistication
 * - faction_stability: Political/social cohesion
 * - resonance_accumulator: The accumulated feedback signal toward cosmic layer
 */
final class CivilizationState
{
    public function __construct(
        public readonly float $culturalEnergy,         // 0.0 to 1.0 (formerly collectiveKnowledge)
        public readonly float $spiritualCohesion,      // 0.0 to 1.0 (formerly ritualCoherence)
        public readonly float $technologicalLevel,     // 0.0 to 2.0
        public readonly float $stability,              // 0.0 to 1.0 (formerly factionStability)
        public readonly float $prosperity,             // 0.0 to 1.0
        public readonly float $militaryPressure,       // 0.0 to 1.0
        public readonly float $externalThreat,         // 0.0 to 1.0
        public readonly float $internalEntropy,        // 0.0 to 1.0
        public readonly float $resonanceAccumulator,   // 0.0 to 1.0
        public readonly float $resilience,             // 0.0 to 1.0
        public readonly int $year,
        public readonly int $yearsInPhase = 0,         // Track duration in current phase for entropy growth
        /** @var \App\Domains\Cosmic\ValueObjects\SocialClass[] */
        public readonly array $socialClasses = [],
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
            resonanceAccumulator: 0.0,
            resilience: 1.0,
            year: $year,
            yearsInPhase: 0,
            socialClasses: [
                new \App\Domains\Cosmic\ValueObjects\SocialClass(\App\Domains\Cosmic\Enums\SocialClassType::NOBILITY, 0.8, 0.9, 0.05),
                new \App\Domains\Cosmic\ValueObjects\SocialClass(\App\Domains\Cosmic\Enums\SocialClassType::PRIESTHOOD, 0.6, 0.8, 0.05),
                new \App\Domains\Cosmic\ValueObjects\SocialClass(\App\Domains\Cosmic\Enums\SocialClassType::WARRIOR, 0.5, 0.7, 0.10),
                new \App\Domains\Cosmic\ValueObjects\SocialClass(\App\Domains\Cosmic\Enums\SocialClassType::MERCHANT, 0.2, 0.5, 0.05),
                new \App\Domains\Cosmic\ValueObjects\SocialClass(\App\Domains\Cosmic\Enums\SocialClassType::PEASANTRY, 0.1, 0.4, 0.75),
                new \App\Domains\Cosmic\ValueObjects\SocialClass(\App\Domains\Cosmic\Enums\SocialClassType::INTELLECTUAL, 0.01, 0.9, 0.01),
            ]
        );
    }

    /**
     * Evolve civilization state based on environmental conditions.
     * Uses internal Euler integration (1-year steps) for numerical stability.
     */
    public function evolve(
        EnvironmentState $envState,
        CosmicState $cosmicState,
        float $agentPerturbation = 0.0,
        int $deltaYears = 100,
        array $modifiers = []
    ): self {
        // Unpack modifiers
        $effBonus = $modifiers['efficiency_bonus'] ?? 0.0;
        $stabMod = $modifiers['stability_modifier'] ?? 0.0;
        $kFactor = $modifiers['knowledge_growth_factor'] ?? 1.0;
        $entResist = $modifiers['entropy_resistance'] ?? 0.0;
        // Local state variables for integration
        $ce = $this->culturalEnergy;
        $sc = $this->spiritualCohesion;
        $tech = $this->technologicalLevel;
        $stab = $this->stability;
        $p = $this->prosperity;
        $mp = $this->militaryPressure;
        $et = $this->externalThreat;
        $ie = $this->internalEntropy;
        
        $res = $this->resonanceAccumulator;
        $r = $this->resilience;
        $yip = $this->yearsInPhase;
        
        $entropy = $cosmicState->entropy;
        
        for ($i = 0; $i < $deltaYears; $i++) {
            // 0. Thermodynamic Efficiency (Non-linear)
            $efficiency = 1.0;
            if ($entropy > 0.6) {
                $effectiveEntropy = max(0, $entropy - $entResist);
                if ($effectiveEntropy > 0.6) {
                    $efficiency = exp(-5.0 * pow($effectiveEntropy - 0.6, 2));
                }
            }
            $efficiency += $effBonus; 

            // 1. Resilience Decay (System Fatigue)
            $rDecay = 0.01 * $entropy * $r;
            $r -= $rDecay;
            $r = max(0.0, min(1.0, $r));
            $effectiveEfficiency = $efficiency * ($r * 0.5 + 0.5); 

            // 2. Cultural Energy (ce)
            $ceGrowth = (0.0001 + ($envState->leyEnergy * 0.0002) + ($stab * 0.0002)) * $effectiveEfficiency * $kFactor;
            $ce = max(0.0, min(1.0, $ce + $ceGrowth - ($ie * 0.0001)));

            // 3. Spiritual Cohesion (sc)
            $scGrowth = ($ce * $stab * 0.0002) * $effectiveEfficiency;
            $scDecay = ($envState->environmentalPressure() * 0.0002) + ($ie * 0.0001);
            $sc = max(0.0, min(1.0, $sc + $scGrowth - $scDecay));

            // 4. Technology
            $techGrowth = ($ce * $envState->biosphereVitality * 0.0001) * $effectiveEfficiency;
            $tech = max(0.0, min(2.0, $tech + $techGrowth));

            // 5. Stability (stab)
            $stabChange = 0.0001 - ($envState->environmentalPressure() * 0.0003) + ($sc * 0.0002) + $stabMod;
            $stabChange -= ($mp * 0.0003); 
            $stabChange -= ($ie * 0.0005); 
            
            $effectivePenaltyEntropy = max(0, $entropy - $entResist);
            if ($effectivePenaltyEntropy > 0.5) {
                $stabChange -= ($effectivePenaltyEntropy - 0.5) * 0.001;
            }
            $stab = max(0.0, min(1.0, $stab + $stabChange));

            // 6. Resonance (Feedback to Cosmic)
            $resGrowth = $sc * abs($cosmicState->cosmicTension()) * 0.001 * $effectiveEfficiency;
            $resDecay = 0.0005; 
            $res = max(0.0, min(1.0, $res + $resGrowth - $resDecay));

            // 7. Prosperity (p)
            $pGrowth = ($tech * 0.1 + $ce * 0.05) * $effectiveEfficiency * $stab * 0.01;
            $pDecay = ($entropy * 0.001) + ($ie * 0.002) + ($mp * 0.001);
            $p = max(0.0, min(1.0, $p + $pGrowth - $pDecay));

            // 8. Internal Entropy (ie)
            $entGrowth = 0.0;
            
            // ENTROPY GROWTH RULE: Golden Age Complacency
            if ($p > 0.8 && $stab > 0.8) {
                $yip++; 
                if ($yip > 20) {
                    $entGrowth += 0.002; // Accelerated entropy for long stability
                } else {
                    $entGrowth += 0.0005; 
                }
            } else {
                $yip = 0; // Reset if stability/prosperity breaks
            }

            if ($stab < 0.3) {
                $entGrowth += 0.002; // Chaos growth
            }
            $ie = max(0.0, min(1.0, $ie + $entGrowth - ($ce * 0.0001))); 

            // 9. Military Pressure (mp) & External Threat (et)
            $et = max(0.0, min(1.0, $et + ($p * 0.0002) - 0.00005));
            $mp = max(0.0, min(1.0, $mp + ($et * 0.01) - ($stab * 0.005)));
        }

        return new self(
            culturalEnergy: round($ce, 6),
            spiritualCohesion: round($sc, 6),
            technologicalLevel: round($tech, 6),
            stability: round($stab, 6),
            prosperity: round($p, 6),
            militaryPressure: round($mp, 6),
            externalThreat: round($et, 6),
            internalEntropy: round($ie, 6),
            resonanceAccumulator: round($res, 6),
            resilience: round($r, 6),
            year: $this->year + $deltaYears,
            yearsInPhase: $yip,
            socialClasses: $this->socialClasses
        );
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
            'resonance_accumulator' => $this->resonanceAccumulator,
            'resilience' => $this->resilience,
            'year' => $this->year,
            'years_in_phase' => $this->yearsInPhase,
            'social_classes' => array_map(fn($c) => $c->toArray(), $this->socialClasses),
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
            resonanceAccumulator: (float) ($data['resonance_accumulator'] ?? 0.0),
            resilience: (float) ($data['resilience'] ?? 1.0),
            year: (int) $data['year'],
            yearsInPhase: (int) ($data['years_in_phase'] ?? 0),
            socialClasses: array_map(
                fn($c) => \App\Domains\Cosmic\ValueObjects\SocialClass::fromArray($c), 
                $data['social_classes'] ?? []
            )
        );
    }

    private function validate(): void
    {
        if ($this->technologicalLevel < 0.0 || $this->technologicalLevel > 2.0) {
            throw new InvalidArgumentException("technologicalLevel range error: {$this->technologicalLevel}");
        }
        foreach (['culturalEnergy', 'spiritualCohesion', 'stability', 'prosperity', 'militaryPressure', 'externalThreat', 'internalEntropy', 'resonanceAccumulator', 'resilience'] as $prop) {
            if ($this->$prop < 0.0 || $this->$prop > 1.0) {
                throw new InvalidArgumentException("{$prop} range error: {$this->$prop}");
            }
        }
    }
}
