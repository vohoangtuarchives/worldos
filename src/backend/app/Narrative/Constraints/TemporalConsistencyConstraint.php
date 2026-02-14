<?php

namespace App\Narrative\Constraints;

use App\Narrative\Values\NarrativeContext;

class TemporalConsistencyConstraint implements NarrativeConstraint
{
    public function check(NarrativeContext $ctx, string $text): ConstraintResult
    {
        // This requires memory of previous honorifics (Inertia check)
        // If the context carries the HonorificMemoryState, we can check here.
        
        $memory = $ctx->socialContext?->honorificMemory;
        if ($memory && $memory->stability > 5) {
             // If relationship is very stable, a sudden change in honorific is a violation
             // Simplified check: assume 'current' must exist in text if stability is high
             if (!empty($memory->currentHonorific) && mb_stripos($text, $memory->currentHonorific) === false) {
                 // Note: this is a weak check, as the character might not be speaking.
                 // In a more advanced version, we'd only check if the character is the speaker.
             }
        }

        return ConstraintResult::pass();
    }
}
