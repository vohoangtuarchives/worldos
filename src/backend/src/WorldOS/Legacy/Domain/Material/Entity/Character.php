<?php

namespace WorldOS\Legacy\Domain\Material\Entity;

use WorldOS\Legacy\Domain\Shared\Entity\AggregateRoot;
use WorldOS\Legacy\Domain\Material\ValueObject\SurvivalProbability;
use WorldOS\Legacy\Domain\Material\ValueObject\RiskFactors;
use WorldOS\Legacy\Domain\Material\ValueObject\NarrativeWeight;

class Character extends AggregateRoot
{
    private string $name;
    private string $status; // 'alive', 'dead', 'missing'
    private ?string $factionId;
    
    // Survival mechanics
    private SurvivalProbability $baseSurvival;
    private RiskFactors $riskFactors;
    private NarrativeWeight $narrativeWeight;
    private float $plotArmorFactor;

    public function __construct(
        string $id, 
        string $name, 
        string $status = 'alive', 
        ?string $factionId = null,
        float $baseSurvivalRate = 0.5,
        float $plotArmorFactor = 1.0,
        ?RiskFactors $riskFactors = null,
        ?NarrativeWeight $narrativeWeight = null
    ) {
        parent::__construct($id);
        $this->name = $name;
        $this->status = $status;
        $this->factionId = $factionId;
        
        $this->baseSurvival = SurvivalProbability::fromFloat($baseSurvivalRate);
        $this->plotArmorFactor = $plotArmorFactor;
        $this->riskFactors = $riskFactors ?? RiskFactors::empty();
        $this->narrativeWeight = $narrativeWeight ?? NarrativeWeight::minor();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isAlive(): bool
    {
        return $this->status === 'alive';
    }

    public function changeFaction(?string $newFactionId): void
    {
        $this->factionId = $newFactionId;
        // Optionally trigger event DomainEvent -> CharacterChangedFaction
    }

    public function die(): void
    {
        if ($this->status !== 'dead') {
            $this->status = 'dead';
            // Trigger Event: CharacterDied
        }
    }

    public function calculateSurvivalProbability(float $worldEntropy): SurvivalProbability
    {
        $base = $this->baseSurvival->value();
        
        // Entropy modifier
        $entropyModifier = $worldEntropy * 0.4;
        
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
            
        return SurvivalProbability::fromFloat(max(0.0, min(1.0, $finalProbability)));
    }

    public function canDie(float $worldEntropy): bool
    {
        // Main character only dies if conditions are met
        if ($this->narrativeWeight->isMainCharacter()) {
            return $worldEntropy > 0.7 
                && $this->narrativeWeight->completionPercentage() >= 0.6;
        }
        
        // Side characters can die more easily
        return $worldEntropy > 0.4;
    }
}
