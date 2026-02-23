<?php

declare(strict_types=1);

namespace WorldOS\Society\Character\Aggregates;

use WorldOS\Society\Character\ValueObject\SurvivalProbability;
use WorldOS\Society\Character\ValueObject\RiskFactors;
use WorldOS\Society\Character\ValueObject\NarrativeWeight;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\EntropyScore;
use WorldOS\Blueprint\Domain\Legacy\Event\ShockEvent;

final class CharacterSurvivalAggregate
{
    private function __construct(
        private readonly string $characterId,
        private SurvivalProbability $baseSurvival,
        private RiskFactors $riskFactors,
        private NarrativeWeight $narrativeWeight,
        private float $plotArmorFactor,
        private bool $isAlive,
    ) {}

    public static function create(
        string $characterId,
        float $baseSurvivalRate,
        float $plotArmorFactor = 1.0
    ): self {
        return new self(
            $characterId,
            new SurvivalProbability($baseSurvivalRate),
            RiskFactors::empty(),
            NarrativeWeight::minor(),
            $plotArmorFactor,
            true
        );
    }

    public function characterId(): string
    {
        return $this->characterId;
    }

    public function isAlive(): bool
    {
        return $this->isAlive;
    }

    public function baseSurvival(): SurvivalProbability
    {
        return $this->baseSurvival;
    }

    public function riskFactors(): RiskFactors
    {
        return $this->riskFactors;
    }

    public function narrativeWeight(): NarrativeWeight
    {
        return $this->narrativeWeight;
    }

    public function plotArmorFactor(): float
    {
        return $this->plotArmorFactor;
    }

    public function updateRiskFactors(RiskFactors $riskFactors): self
    {
        return new self(
            $this->characterId,
            $this->baseSurvival,
            $riskFactors,
            $this->narrativeWeight,
            $this->plotArmorFactor,
            $this->isAlive
        );
    }

    public function updateNarrativeWeight(NarrativeWeight $weight): self
    {
        return new self(
            $this->characterId,
            $this->baseSurvival,
            $this->riskFactors,
            $weight,
            $this->plotArmorFactor,
            $this->isAlive
        );
    }

    public function adjustPlotArmor(float $factor): self
    {
        return new self(
            $this->characterId,
            $this->baseSurvival,
            $this->riskFactors,
            $this->narrativeWeight,
            max(0.0, $factor),
            $this->isAlive
        );
    }

    public function calculateSurvivalProbability(EntropyScore $worldEntropy): SurvivalProbability
    {
        $base = $this->baseSurvival->value();
        
        // Entropy modifier
        $entropyModifier = $worldEntropy->value() * 0.4;
        
        // Risk factors
        $riskModifier = $this->riskFactors->totalRisk() * 0.3;
        
        // Plot armor protection
        $armorProtection = $this->plotArmorFactor * 0.2;
        
        // Narrative weight protection
        $narrativeProtection = $this->narrativeWeight->protectionFactor() * 0.1;
        
        $finalProbability = $base
            - $entropyModifier
            - $riskModifier
            + $armorProtection
            + $narrativeProtection;
            
        return new SurvivalProbability(max(0.0, min(1.0, $finalProbability)));
    }

    public function applyShockEvent(ShockEvent $event): self
    {
        $newRiskFactors = $this->riskFactors->applyShock($event);
        
        return new self(
            $this->characterId,
            $this->baseSurvival,
            $newRiskFactors,
            $this->narrativeWeight,
            $this->plotArmorFactor,
            $this->isAlive
        );
    }

    public function markAsDead(): self
    {
        return new self(
            $this->characterId,
            $this->baseSurvival,
            $this->riskFactors,
            $this->narrativeWeight,
            $this->plotArmorFactor,
            false
        );
    }

    public function canDie(EntropyScore $worldEntropy): bool
    {
        // Main character only dies if conditions are met
        if ($this->narrativeWeight->isMainCharacter()) {
            return $worldEntropy->value() > 0.7 
                && $this->narrativeWeight->completionPercentage() >= 0.6;
        }
        
        // Side characters can die more easily
        return $worldEntropy->value() > 0.4;
    }

    public function toArray(): array
    {
        return [
            'character_id' => $this->characterId,
            'base_survival' => $this->baseSurvival->value(),
            'risk_factors' => $this->riskFactors->toArray(),
            'narrative_weight' => $this->narrativeWeight->toArray(),
            'plot_armor_factor' => $this->plotArmorFactor,
            'is_alive' => $this->isAlive,
        ];
    }
}
