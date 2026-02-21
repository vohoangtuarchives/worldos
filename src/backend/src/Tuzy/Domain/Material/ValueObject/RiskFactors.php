<?php

namespace Tuzy\Domain\Material\ValueObject;

final readonly class RiskFactors
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

    public function totalRisk(): float
    {
        return ($this->injuryState * 0.25)
            + ($this->environmentalDanger * 0.20)
            + ($this->politicalInstability * 0.20)
            + ($this->resourceScarcity * 0.15)
            + ($this->factionThreat * 0.10)
            + ($this->mythCorruption * 0.10);
    }

    private function validateRanges(): void
    {
        $factors = [
            'injury_state' => $this->injuryState,
            'environmental_danger' => $this->environmentalDanger,
            'political_instability' => $this->politicalInstability,
            'resource_scarcity' => $this->resourceScarcity,
            'faction_threat' => $this->factionThreat,
            'myth_corruption' => $this->mythCorruption,
        ];

        foreach ($factors as $name => $value) {
            if ($value < 0.0 || $value > 1.0) {
                throw new \InvalidArgumentException("Risk factor {$name} must be between 0 and 1, got {$value}");
            }
        }
    }
}
