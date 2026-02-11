<?php

namespace App\Domains\History\Services;

use App\Models\Scar;
use App\Models\Faction;
use App\Models\World;

class ScarImpactService
{
    public function __construct(
        private \App\Domains\History\Repositories\ScarRepositoryInterface $scarRepository
    ) {}

    /**
     * Calculate the net belief shift vector for a faction at a specific tick.
     * Considers all active scars and their counterforces.
     */
    public function calculateFactionIdeologyDrift(Faction $faction, int $currentTick): array
    {
        // Active scars affecting this faction (Global + Faction specific)
        $scars = $this->scarRepository->findActiveScarsForFaction($faction->world_id, $faction->id);

        $netImpact = [
            'militarism' => 0.0,
            'spiritualism' => 0.0,
            'expansionism' => 0.0,
            'collectivism' => 0.0,
            'purity' => 0.0,
        ];

        foreach ($scars as $scar) {
            $age = $currentTick - $scar->created_tick;
            if ($age < 0) continue;

            // Deterministic decay: exp(-decay_rate * age)
            $decay = exp(-$scar->decay_rate * $age);

            foreach ($scar->belief_shift_vector as $key => $value) {
                if (!isset($netImpact[$key])) continue;

                $rawImpact = $value * $decay;
                
                // Calculate total healing for this dimension
                $healingImpact = 0;
                foreach ($scar->counterforces as $cf) {
                    if (isset($cf->healing_vector[$key])) {
                        $healingImpact += $cf->healing_vector[$key];
                    }
                }
                
                // Healing neutralizes the impact (moves it towards 0)
                if ($rawImpact > 0) {
                   $effective = max(0.0, $rawImpact - $healingImpact);
                } else {
                   $effective = min(0.0, $rawImpact + $healingImpact);
                }
                
                $netImpact[$key] += $effective;
            }
        }

        return $netImpact;
    }

    /**
     * Calculate global entropy contribution from Scars.
     * Entropy = Sum(ActiveScar.Impact * AgeDecay) - Sum(Healing.Strength)
     */
    public function calculateGlobalEntropyContribution(World $world, int $currentTick): float
    {
        $scars = $this->scarRepository->findActiveScarsForWorld($world->id);

        $totalEntropy = 0.0;

        foreach ($scars as $scar) {
            $age = $currentTick - $scar->created_tick;
            if ($age < 0) continue;

            $decay = exp(-$scar->decay_rate * $age);
            
            // Scar Contribution
            $scarEntropy = $scar->pain_score * $decay;

            // Healing Deduction
            $healingDeduction = 0.0;
            foreach ($scar->counterforces as $cf) {
                $healingDeduction += $cf->strength;
            }

            // Net contribution cannot be negative (healing can't reduce entropy below 0 for a scar)
            $netScarEntropy = max(0.0, $scarEntropy - $healingDeduction);
            
            $totalEntropy += $netScarEntropy;
        }

        return $totalEntropy;
    }
}
