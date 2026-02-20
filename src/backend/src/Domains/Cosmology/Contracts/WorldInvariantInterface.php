<?php

declare(strict_types=1);

namespace WorldOS\Domains\Cosmology\Contracts;

use WorldOS\Domains\Cosmology\World;

/**
 * WorldInvariantInterface
 * 
 * Defines a physical or logical rule that a World's state must never violate.
 */
interface WorldInvariantInterface
{
    /**
     * Validate the world state against this invariant.
     */
    public function validate(World $world): bool;

    /**
     * Get the error message if validation fails.
     */
    public function getViolationMessage(): ?string;
}
