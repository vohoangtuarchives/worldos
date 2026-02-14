<?php

namespace App\Domains\World\Support;

class DeterministicRandom
{
    private \Random\Randomizer $randomizer;

    public function __construct(int $seed, int $tick)
    {
        // Combine seed and tick to create a unique seed for this tick
        // We use a simple combination, but could be more complex hash
        // Using distinct seed for every tick ensures determinism
        $combinedSeed = $seed + $tick;
        $engine = new \Random\Engine\Mt19937($combinedSeed);
        $this->randomizer = new \Random\Randomizer($engine);
    }

    public function float(float $min = 0.0, float $max = 1.0): float
    {
        return $this->randomizer->getFloat($min, $max);
    }

    public function int(int $min, int $max): int
    {
        return $this->randomizer->getInt($min, $max);
    }

    public function chance(float $probability): bool
    {
        return $this->float() < $probability;
    }

    public function element(array $array): mixed
    {
        if (empty($array)) {
            return null;
        }
        $keys = array_keys($array);
        $key = $keys[$this->int(0, count($keys) - 1)];
        return $array[$key];
    }
}
