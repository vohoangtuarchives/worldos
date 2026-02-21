<?php

namespace Tuzy\Domain\World\Contracts;

use App\Models\World;

interface WorldInvariantInterface
{
    /**
     * Check if the world state obeys the laws of physics.
     * 
     * @param World $world
     * @return bool True if valid, False if corrupted
     */
    public function validate(World $world): bool;

    /**
     * Get the specific violation message if validation fails.
     */
    public function getViolationMessage(): ?string;
}
