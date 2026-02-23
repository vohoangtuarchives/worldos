<?php

declare(strict_types=1);

namespace WorldOS\Society\Character\ValueObject;

use InvalidArgumentException;
use WorldOS\Blueprint\Domain\Legacy\Event\ShockEvent;

readonly class RiskFactors
{
    private function __construct(
        private float $injuryState,
        private float $environmentalDanger,
        private float $politicalInstability,
        private float $resourceScarcity,
        private float $factionThreat,
        private float $mythCorruption,
    ) {
        $this->validateRanges();
    }

    public static function empty(): self
    {
        return new self(0.0, 0.0, 0.0, 0.0, 0.0, 0.0);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['injury_state'] ?? 0.0),
            (float) ($data['environmental_danger'] ?? 0.0),
            (float) ($data['political_instability'] ?? 0.0),
            (float) ($data['resource_scarcity'] ?? 0.0),
            (float) ($data['faction_threat'] ?? 0.0),
            (float) ($data['myth_corruption'] ?? 0.0),
        );
    }

    public function injuryState(): float { return $this->injuryState; }
    public function environmentalDanger(): float { return $this->environmentalDanger; }
    public function politicalInstability(): float { return $this->politicalInstability; }
    public function resourceScarcity(): float { return $this->resourceScarcity; }
    public function factionThreat(): float { return $this->factionThreat; }
    public function mythCorruption(): float { return $this->mythCorruption; }

    public function totalRisk(): float
    {
        return ($this->injuryState * 0.25) + ($this->environmentalDanger * 0.20) + ($this->politicalInstability * 0.20)
            + ($this->resourceScarcity * 0.15) + ($this->factionThreat * 0.10) + ($this->mythCorruption * 0.10);
    }

    public function applyShock(ShockEvent $event): self
    {
        $modifiers = array_merge(
            ['injury' => 0.0, 'environmental' => 0.0, 'political' => 0.0, 'resource' => 0.0, 'faction' => 0.0, 'myth' => 0.0],
            $event->riskModifiers()
        );
        return new self(
            min(1.0, $this->injuryState + ($modifiers['injury'] ?? 0.0)),
            min(1.0, $this->environmentalDanger + ($modifiers['environmental'] ?? 0.0)),
            min(1.0, $this->politicalInstability + ($modifiers['political'] ?? 0.0)),
            min(1.0, $this->resourceScarcity + ($modifiers['resource'] ?? 0.0)),
            min(1.0, $this->factionThreat + ($modifiers['faction'] ?? 0.0)),
            min(1.0, $this->mythCorruption + ($modifiers['myth'] ?? 0.0)),
        );
    }

    public function withInjury(float $injury): self
    {
        return new self(min(1.0, $injury), $this->environmentalDanger, $this->politicalInstability, $this->resourceScarcity, $this->factionThreat, $this->mythCorruption);
    }

    public function withEnvironmentalDanger(float $danger): self
    {
        return new self($this->injuryState, min(1.0, $danger), $this->politicalInstability, $this->resourceScarcity, $this->factionThreat, $this->mythCorruption);
    }

    public function toArray(): array
    {
        return [
            'injury_state' => $this->injuryState,
            'environmental_danger' => $this->environmentalDanger,
            'political_instability' => $this->politicalInstability,
            'resource_scarcity' => $this->resourceScarcity,
            'faction_threat' => $this->factionThreat,
            'myth_corruption' => $this->mythCorruption,
            'total_risk' => $this->totalRisk(),
        ];
    }

    private function validateRanges(): void
    {
        foreach (['injury_state' => $this->injuryState, 'environmental_danger' => $this->environmentalDanger, 'political_instability' => $this->politicalInstability, 'resource_scarcity' => $this->resourceScarcity, 'faction_threat' => $this->factionThreat, 'myth_corruption' => $this->mythCorruption] as $name => $value) {
            if ($value < 0.0 || $value > 1.0) {
                throw new InvalidArgumentException("Risk factor {$name} must be between 0 and 1, got {$value}");
            }
        }
    }
}
