<?php

namespace WorldOS\Legacy\Application\History\Services;

use App\Models\World;
use App\Models\Faction;
use WorldOS\Legacy\Application\History\Services\ScarImpactService;
use Illuminate\Support\Collection;

class EntropyCalculator
{
    public function __construct(
        private ScarImpactService $scarService
    ) {}

    /**
     * Calculate the current entropy of the world.
     * Formula: Base Entropy (Factions) + Historical Entropy (Scars) - Regulatory Order (Institutions)
     * 
     * @param World $world
     * @param int $currentTick
     * @return float Normalized entropy score (0.0 to 1.0+)
     */
    public function calculateWorldEntropy(World $world, int $currentTick): float
    {
        $factions = Faction::where('world_id', $world->id)->get();
        if ($factions->isEmpty()) {
            return 0.0;
        }

        // 1. Base Entropy (The present tension)
        // Ideally this comes from a FactionAnalyzer, but we implement a simplified version here.
        // ID: Ideological Divergence
        // FPI: Faction Power Imbalance
        
        $baseEntropy = $this->calculateBaseEntropy($factions);

        // 2. Historical Entropy (The weight of the past)
        // Scars add entropy (trauma, unresolved conflict).
        // Healing reduces this specific component (integrated trauma).
        // This method returns (Scars - Healing).
        $historicalEntropy = $this->scarService->calculateGlobalEntropyContribution($world, $currentTick);

        // 3. Institutional Order (The Regulator)
        // Active institutions reduce *perception* of entropy or suppress it.
        // We can model this as a direct subtraction or a dampener.
        // Let's treat it as a stabilizing force subtracted from the total.
        $institutionalOrder = $this->calculateInstitutionalOrder($world);

        $totalEntropy = $baseEntropy + $historicalEntropy - $institutionalOrder;

        return max(0.0, $totalEntropy);
    }

    private function calculateBaseEntropy(Collection $factions): float
    {
        // Simple metric: Variance in ideology + Variance in resources
        // High variance = High conflict potential = High entropy? 
        // Or is it Stability = Balance? 
        // Usually, 1 hegemon = Low Entropy (Order). Many equal warring states = High Entropy.
        // Let's use Ideological Divergence as the main driver.
        
        $centroid = [
            'militarism' => $factions->avg(fn($f) => $f->ideology['militarism'] ?? 0),
            'spiritualism' => $factions->avg(fn($f) => $f->ideology['spiritualism'] ?? 0),
            'expansionism' => $factions->avg(fn($f) => $f->ideology['expansionism'] ?? 0),
            'collectivism' => $factions->avg(fn($f) => $f->ideology['collectivism'] ?? 0),
            'purity' => $factions->avg(fn($f) => $f->ideology['purity'] ?? 0),
        ];

        $totalDivergence = 0.0;
        foreach ($factions as $faction) {
            $dist = 0.0;
            foreach ($centroid as $key => $val) {
                $dist += pow(($faction->ideology[$key] ?? 0) - $val, 2);
            }
            $totalDivergence += sqrt($dist);
        }

        return $totalDivergence / $factions->count();
    }

    private function calculateInstitutionalOrder(World $world): float
    {
        // Sum of (Authority * Trust) of all active institutions
        // This represents the capacity of the system to absorb chaos.
        $institutions = \App\Models\Institution::where('world_id', $world->id)->get();
        
        $totalOrder = 0.0;
        foreach ($institutions as $inst) {
            $totalOrder += $inst->authority_level * $inst->public_trust;
        }

        // Normalize? Or keep as raw suppression power?
        // Let's say a strong institution contributes 0.1 to 0.5 reduction.
        return $totalOrder * 0.2; 
    }
}
