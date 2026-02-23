<?php

namespace WorldOS\Legacy\Application\Narrative\Dialogue\Rules;

use WorldOS\Legacy\Application\Narrative\Character\Character;
use WorldOS\Legacy\Application\Narrative\Dialogue\Contracts\GuardRule;
use WorldOS\Saga\Domain\Narrative\ValueObject\Intent;
use WorldOS\Legacy\Application\Narrative\Scene\Scene;

class CannotRevealUnknownInformation implements GuardRule
{
    public function allows(Character $actor, Intent $intent, Scene $scene): bool
    {
        // Only applies to REVEAL intents
        if ($intent->type !== 'REVEAL') {
            return true;
        }

        // Check payload for specific fact ID or content
        $fact = $intent->payload['fact'] ?? null;
        if (!$fact) {
            return true; // No fact specified, passing (or fail depending on strictness)
        }

        // Check if actor knows this fact
        // Rudimentary check: iterate memories. In real system, use vector search or ID map.
        foreach ($actor->getMemories()->all() as $memory) {
            // Simple string contains check for MVP
            if (str_contains($memory->content, $fact)) {
                return true;
            }
        }

        return false;
    }

    public function failureReason(): string
    {
        return "Character cannot reveal information they do not know.";
    }
}
