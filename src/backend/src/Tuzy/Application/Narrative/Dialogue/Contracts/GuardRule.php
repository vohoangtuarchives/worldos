<?php

namespace Tuzy\Application\Narrative\Dialogue\Contracts;

use Tuzy\Application\Narrative\Character\Character;
use Tuzy\Domain\Narrative\ValueObject\Intent;
use Tuzy\Application\Narrative\Scene\Scene; // We will define Scene next

interface GuardRule
{
    /**
     * Check if the intent is allowed.
     * 
     * @param Character $actor
     * @param Intent $intent
     * @param Scene $scene
     * @return bool
     */
    public function allows(Character $actor, Intent $intent, Scene $scene): bool;

    /**
     * Reason for rejection (used for feedback loop to LLM).
     * 
     * @return string
     */
    public function failureReason(): string;
}
