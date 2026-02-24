<?php

namespace WorldOS\Legacy\Domain\Cosmology\Entity;

use InvalidArgumentException;

readonly class WorldSeed
{
    public function __construct(
        public Archetype $archetype,
        public float $ontologyVector,
        public float $epistemicVector,
        public float $civilizationVector,
        public float $energyVector
    ) {
        $this->validateVector('ontologyVector', $this->ontologyVector);
        $this->validateVector('epistemicVector', $this->epistemicVector);
        $this->validateVector('civilizationVector', $this->civilizationVector);
        $this->validateVector('energyVector', $this->energyVector);
    }

    private function validateVector(string $name, float $value): void
    {
        if ($value < 0.0 || $value > 1.0) {
            throw new InvalidArgumentException("{$name} must be between 0.0 and 1.0");
        }
    }
}
