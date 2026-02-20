<?php

namespace WorldOS\Domains\Material;

use WorldOS\Domains\Shared\AggregateRoot;
use WorldOS\Domains\Material\ValueObjects\IdeologyVector;
use WorldOS\Domains\Material\ValueObjects\FactionMemory;

class Faction extends AggregateRoot
{
    private string $name;
    private string $type; // 'empire', 'guild', 'sect', 'tribe', etc.
    private float $powerLevel;
    private IdeologyVector $ideology;
    private FactionMemory $memory;

    public function __construct(
        string $id, 
        string $name, 
        string $type, 
        float $initialPower = 1.0,
        ?IdeologyVector $ideology = null,
        ?FactionMemory $memory = null
    ) {
        parent::__construct($id);
        $this->name = $name;
        $this->type = $type;
        $this->powerLevel = $initialPower;
        $this->ideology = $ideology ?? IdeologyVector::default();
        $this->memory = $memory ?? FactionMemory::fresh();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPowerLevel(): float
    {
        return $this->powerLevel;
    }

    public function modifyPower(float $delta): void
    {
        $this->powerLevel += $delta;
        
        if ($this->powerLevel <= 0) {
            $this->powerLevel = 0;
            // Optionally trigger Event: FactionCollapsed
        }
    }

    public function getIdeology(): IdeologyVector
    {
        return $this->ideology;
    }

    public function getMemory(): FactionMemory
    {
        return $this->memory;
    }

    public function mutateIdeology(float $variance = 0.05): void
    {
        $this->ideology = $this->ideology->mutate($variance);
    }

    public function recordIntent(string $intent, bool $isSuccess): void
    {
        $this->memory->recordIntent($intent, $isSuccess);
        
        if ($isSuccess) {
            $this->memory->increaseSuccessScore(0.1);
        } else {
            $this->memory->increaseMythBacklash(0.1);
            $this->memory->increaseWarFatigue(0.05);
        }
    }

    public function decayMemory(float $rate = 0.1): void
    {
        $this->memory->decayFatigue($rate);
    }

    public function resetMemory(): void
    {
        $this->memory = FactionMemory::fresh();
    }
}
