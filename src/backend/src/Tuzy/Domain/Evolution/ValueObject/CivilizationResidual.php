<?php

namespace Tuzy\Domain\Evolution\ValueObject;

class CivilizationResidual
{
    public function __construct(
        public float $warTrauma = 0.0,
        public float $metaphysicalScar = 0.0,
        public float $socialUnrest = 0.0,
        public float $decayRate = 0.05, // default 5% decay per event/era
        public float $cumulativeTrauma = 0.0 // The Eternal Memory of the World
    ) {}

    public function addTrauma(float $amount, string $type): void
    {
        $this->cumulativeTrauma += $amount; // Always accumulates
        
        match ($type) {
            'war' => $this->warTrauma += $amount,
            'metaphysical' => $this->metaphysicalScar += $amount,
            'social' => $this->socialUnrest += $amount,
            default => null
        };
    }

    public function decreaseTrauma(float $amount): void
    {
        $this->warTrauma = max(0.0, $this->warTrauma - ($amount * 0.33));
        $this->metaphysicalScar = max(0.0, $this->metaphysicalScar - ($amount * 0.33));
        $this->socialUnrest = max(0.0, $this->socialUnrest - ($amount * 0.33));
    }

    /**
     * Called whenever a major time period passes or an event attempts to clear history.
     */
    public function decay(): void
    {
        $this->warTrauma = max(0.0, $this->warTrauma - ($this->warTrauma * $this->decayRate));
        $this->metaphysicalScar = max(0.0, $this->metaphysicalScar - ($this->metaphysicalScar * $this->decayRate));
        $this->socialUnrest = max(0.0, $this->socialUnrest - ($this->socialUnrest * $this->decayRate));
    }

    public function getTotalPressure(): float
    {
        return $this->warTrauma + $this->metaphysicalScar + $this->socialUnrest;
    }
}


