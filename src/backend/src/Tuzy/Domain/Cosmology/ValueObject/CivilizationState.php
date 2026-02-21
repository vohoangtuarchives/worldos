<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmology\ValueObject;

use Tuzy\Domain\Cosmology\Enums\SocialClassType;
use InvalidArgumentException;

final class CivilizationState
{
    public function __construct(
        public readonly float $culturalEnergy,
        public readonly float $spiritualCohesion,
        public readonly float $technologicalLevel,
        public readonly float $stability,
        public readonly float $prosperity,
        public readonly float $militaryPressure,
        public readonly float $externalThreat,
        public readonly float $internalEntropy,
        public readonly float $resonanceAccumulator,
        public readonly float $resilience,
        public readonly int $year,
        public readonly int $yearsInPhase = 0,
        /** @var SocialClass[] */
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
                new SocialClass(SocialClassType::NOBILITY, 0.8, 0.9, 0.05),
                new SocialClass(SocialClassType::PRIESTHOOD, 0.6, 0.8, 0.05),
                new SocialClass(SocialClassType::WARRIOR, 0.5, 0.7, 0.10),
                new SocialClass(SocialClassType::MERCHANT, 0.2, 0.5, 0.05),
                new SocialClass(SocialClassType::PEASANTRY, 0.1, 0.4, 0.75),
                new SocialClass(SocialClassType::INTELLECTUAL, 0.01, 0.9, 0.01),
            ]
        );
    }

    public function evolve(
        EnvironmentState $envState,
        CosmicState $cosmicState,
        float $agentPerturbation = 0.0,
        int $deltaYears = 100,
        array $modifiers = []
    ): self {
        $effBonus = $modifiers['efficiency_bonus'] ?? 0.0;
        $stabMod = $modifiers['stability_modifier'] ?? 0.0;
        $kFactor = $modifiers['knowledge_growth_factor'] ?? 1.0;
        $entResist = $modifiers['entropy_resistance'] ?? 0.0;
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
            $efficiency = 1.0;
            if ($entropy > 0.6) {
                $effectiveEntropy = max(0, $entropy - $entResist);
                if ($effectiveEntropy > 0.6) {
                    $efficiency = exp(-5.0 * pow($effectiveEntropy - 0.6, 2));
                }
            }
            $efficiency += $effBonus;
            $rDecay = 0.01 * $entropy * $r;
            $r -= $rDecay;
            $r = max(0.0, min(1.0, $r));
            $effectiveEfficiency = $efficiency * ($r * 0.5 + 0.5);
            $ceGrowth = (0.0001 + ($envState->leyEnergy * 0.0002) + ($stab * 0.0002)) * $effectiveEfficiency * $kFactor;
            $ce = max(0.0, min(1.0, $ce + $ceGrowth - ($ie * 0.0001)));
            $scGrowth = ($ce * $stab * 0.0002) * $effectiveEfficiency;
            $scDecay = ($envState->environmentalPressure() * 0.0002) + ($ie * 0.0001);
            $sc = max(0.0, min(1.0, $sc + $scGrowth - $scDecay));
            $techGrowth = ($ce * $envState->biosphereVitality * 0.0001) * $effectiveEfficiency;
            $tech = max(0.0, min(2.0, $tech + $techGrowth));
            $stabChange = 0.0001 - ($envState->environmentalPressure() * 0.0003) + ($sc * 0.0002) + $stabMod;
            $stabChange -= ($mp * 0.0003);
            $stabChange -= ($ie * 0.0005);
            $effectivePenaltyEntropy = max(0, $entropy - $entResist);
            if ($effectivePenaltyEntropy > 0.5) {
                $stabChange -= ($effectivePenaltyEntropy - 0.5) * 0.001;
            }
            $stab = max(0.0, min(1.0, $stab + $stabChange));
            $resGrowth = $sc * abs($cosmicState->cosmicTension()) * 0.001 * $effectiveEfficiency;
            $resDecay = 0.0005;
            $res = max(0.0, min(1.0, $res + $resGrowth - $resDecay));
            $pGrowth = ($tech * 0.1 + $ce * 0.05) * $effectiveEfficiency * $stab * 0.01;
            $pDecay = ($entropy * 0.001) + ($ie * 0.002) + ($mp * 0.001);
            $p = max(0.0, min(1.0, $p + $pGrowth - $pDecay));
            $entGrowth = 0.0;
            if ($p > 0.8 && $stab > 0.8) {
                $yip++;
                $entGrowth += $yip > 20 ? 0.002 : 0.0005;
            } else {
                $yip = 0;
            }
            if ($stab < 0.3) {
                $entGrowth += 0.002;
            }
            $ie = max(0.0, min(1.0, $ie + $entGrowth - ($ce * 0.0001)));
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
        return max(0.0, min(1.0, $this->technologicalLevel * 0.3 - min($this->culturalEnergy * 0.1, 0.15)));
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
            socialClasses: array_map(fn($c) => SocialClass::fromArray($c), $data['social_classes'] ?? [])
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
