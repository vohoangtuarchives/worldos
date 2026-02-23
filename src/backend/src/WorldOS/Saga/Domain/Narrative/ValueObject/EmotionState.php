<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Narrative\ValueObject;

/**
 * Character emotion: type, intensity (0–1), decay rate.
 */
readonly class EmotionState
{
    public function __construct(
        public string $type = 'neutral',
        public float $intensity = 0.0,
        public float $decayRate = 0.1,
    ) {
    }

    public function amplify(float $delta): self
    {
        return new self(
            $this->type,
            min(1.0, $this->intensity + $delta),
            $this->decayRate,
        );
    }

    public function decay(): self
    {
        return new self(
            $this->type,
            max(0.0, $this->intensity * (1.0 - $this->decayRate)),
            $this->decayRate,
        );
    }
}
