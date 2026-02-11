<?php

namespace App\Domains\History\Policies;

use App\Models\Myth;
use App\Models\Faction;

class MythRewritePolicy
{
    /**
     * Determine if a myth can be rewritten based on current world state.
     */
    public function shouldRewrite(Myth $myth, int $currentTick, float $ideologyDrift, float $entropy): bool
    {
        // 1. Minimum Age Rule: Myths need time to settle before being rewritten
        $age = $currentTick - $myth->created_tick;
        if ($age < 50) {
            return false;
        }

        // 2. High Drift Rule: If the world's ideology has shifted significantly since myth creation,
        // the myth is "outdated" and vulnerable to rewriting.
        if ($ideologyDrift > 0.4) {
            return true;
        }

        // 3. High Entropy Rule: In chaos, myths are unstable.
        if ($entropy > 0.7) {
            return true;
        }

        return false;
    }

    /**
     * Determine if a specific faction has the power to force a rewrite (Propaganda).
     */
    public function canFactionForceRewrite(Faction $faction, Myth $myth): bool
    {
        // Faction needs high resources or high "myth_authority" (if we had that metric)
        // For now, simple check on resources/influence.
        // Assuming 'resources' is a simple value or array.
        // Let's assume we pass a "power_score" or similar.
        
        // Placeholder logic
        return false;
    }
}
