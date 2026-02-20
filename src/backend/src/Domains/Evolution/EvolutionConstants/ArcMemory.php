<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\EvolutionConstants;

/**
 * Tracks current arc phase and ticks in phase for hysteresis (avoid flip-flop).
 */
final class ArcMemory
{
    public const MIN_TICKS_BEFORE_TRANSITION = 5;

    public function __construct(
        public ArcPhase $current,
        public int $ticksInPhase = 0
    ) {
    }

    public function considerTransition(ArcPhase $detected): ArcPhase
    {
        if ($detected === $this->current) {
            return $this->current;
        }
        if ($this->ticksInPhase < self::MIN_TICKS_BEFORE_TRANSITION) {
            return $this->current;
        }
        return $detected;
    }

    public function advance(ArcPhase $phase): self
    {
        if ($phase === $this->current) {
            return new self($this->current, $this->ticksInPhase + 1);
        }
        return new self($phase, 1);
    }
}


