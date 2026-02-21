<?php

namespace App\Domains\Faction\Services;

use App\Models\Faction;
use Tuzy\Domain\Faction\ValueObject\Leader;

class SuccessionService
{
    /**
     * Handle aging and potential death of a leader.
     */
    public function handleSuccession(Faction $faction): void
    {
        $leader = $faction->getLeader();
        $leader->age();

        $deathProbability = $this->calculateDeathProbability($leader);

        if (mt_rand(0, 1000) < ($deathProbability * 1000)) {
            $this->triggerSuccession($faction, $leader);
        } else {
            $faction->updateLeader($leader);
            $faction->save();
        }
    }

    private function calculateDeathProbability(Leader $leader): float
    {
        if ($leader->age < 50) return 0.001; // Very low
        if ($leader->age < 70) return 0.01;  // Low
        if ($leader->age < 85) return 0.05;  // Moderate
        
        return 0.2; // High (Approaching natural end)
    }

    private function triggerSuccession(Faction $faction, Leader $oldLeader): void
    {
        $newLeader = Leader::create(
            $oldLeader->generation + 1,
            $oldLeader->personality
        );

        $faction->updateLeader($newLeader);
        $faction->save();

        // Succession event could be logged here or dispatched
    }
}
