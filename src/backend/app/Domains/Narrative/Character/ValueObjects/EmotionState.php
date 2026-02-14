<?php

namespace App\Domains\Narrative\Character\ValueObjects;

class EmotionState
{
    public function __construct(
        public readonly string $type,
        public readonly float $intensity, // 0.0 to 1.0
        public readonly float $decayRate
    ) {}

    public function amplify(float $delta): self
    {
        return new self(
            $this->type,
            min(1.0, $this->intensity + $delta),
            $this->decayRate
        );
    }

    public function decay(): self
    {
        return new self(
            $this->type,
            max(0.0, $this->intensity * (1.0 - $this->decayRate)), // Decay by rate (e.g. 0.1 means -10%)
            $this->decayRate
        );
    }
}
