<?php

namespace App\StoryEngine\Balancing;

class WorldHealth
{
    public function __construct(
        public int $activeFactions,
        public int $economicStability, // 0-100 (Higher is good)
        public int $conflictLevel,     // 0-100 (Higher is bad)
        public int $populationStress   // 0-100 (Higher is bad)
    ) {}

    public function dangerScore(): int
    {
        // Simple heuristic: Average of bad things
        // (100 - Stability) + Conflict + Stress / 3
        return (
            (100 - $this->economicStability)
            + $this->conflictLevel
            + $this->populationStress
        ) / 3;
    }
}
