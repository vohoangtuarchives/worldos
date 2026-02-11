<?php

namespace App\StoryEngine;

class Seed
{
    public string $type;
    public string $dimension;
    public int $severity;
    public int $age = 0;

    public function __construct(string $type, string $dimension, int $severity)
    {
        $this->type = $type;
        $this->dimension = $dimension;
        $this->severity = $severity;
    }

    public function getDimensionLevel(): int
    {
        return match ($this->dimension) {
            'personal' => 0,
            'family' => 1,
            'faction' => 2,
            'city' => 3,
            'world' => 4,
            default => 0,
        };
    }

    public static function getDimensionFromLevel(int $level): string
    {
        return match ($level) {
            0 => 'personal',
            1 => 'family',
            2 => 'faction',
            3 => 'city',
            4 => 'world',
            default => 'personal',
        };
    }

    public function score(): int
    {
        // Simple score: severity + age
        // Higher score = more urgent
        return $this->severity + $this->age;
    }
}
