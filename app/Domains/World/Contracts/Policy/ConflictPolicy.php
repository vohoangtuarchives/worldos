<?php

namespace App\Domains\World\Contracts\Policy;

interface ConflictPolicy
{
    /**
     * Determine if a conflict should arise between two entities or factions.
     */
    public function shouldConflict(array $snapshot, string $aggressorId, string $defenderId): bool;
}
