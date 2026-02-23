<?php

namespace App\Domains\Narrative\Dialogue\Contracts;

use App\Domains\Narrative\Character\Character;
use WorldOS\Saga\Domain\Narrative\ValueObject\Intent;
use App\Domains\Narrative\Scene\Scene; // We will define Scene next

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
