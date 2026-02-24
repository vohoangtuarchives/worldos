<?php

namespace App\StoryEngine;

class Economy
{
    public int $food = 100;
    public int $energy = 100;
    public int $materials = 100;

    public function __construct(int $initial = 100)
    {
        $this->food = $initial;
        $this->energy = $initial;
        $this->materials = $initial;
    }

    public function stressLevel(): int
    {
        // 0 to 100
        // Stress increases as resources drop.
        // If any resource is 0, stress is 100.
        return max(
            0,
            100 - min($this->food, $this->energy, $this->materials)
        );
    }
    
    public function consume(int $amount): void
    {
        $this->food = max(0, $this->food - $amount);
        $this->energy = max(0, $this->energy - $amount);
        $this->materials = max(0, $this->materials - $amount);
    }
    
    public function produce(int $amount): void
    {
        $this->food += $amount;
        $this->energy += $amount;
        $this->materials += $amount;
    }
}
