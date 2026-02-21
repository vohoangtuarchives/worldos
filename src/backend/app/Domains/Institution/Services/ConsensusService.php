<?php

namespace App\Domains\Institution\Services;

use App\Models\Institution;
use App\Models\Faction;
use Tuzy\Domain\Faction\ValueObject\IdeologyVector;

class ConsensusService
{
    /**
     * Calculate the "Hidden Compliance" of a faction towards an institution.
     * Compliance is inversely proportional to the Euclidean distance between
     * the Faction's ideology and the Institution's charter values.
     * 
     * Returns a float between 0.0 (Total Dissent) and 1.0 (Total Alignment).
     */
    public function calculateHiddenCompliance(Institution $institution, Faction $faction): float
    {
        $charter = $institution->charter_values;
        $ideology = $faction->getIdeology(); // Returns IdeologyVector

        // Charter values might be a subset of the full 5-axis ideology.
        // We only compare the dimensions present in the charter.
        
        $squaredDifferenceSum = 0.0;
        $dimensionsCount = 0;

        foreach ($charter as $axis => $targetValue) {
            $factionValue = $this->getFactionAxisValue($ideology, $axis);
            $squaredDifferenceSum += pow($targetValue - $factionValue, 2);
            $dimensionsCount++;
        }

        if ($dimensionsCount === 0) {
            return 1.0; // No charter? No conflict.
        }

        $distance = sqrt($squaredDifferenceSum);
        // Max possible distance in N dimensions (assuming 0-1 range) is sqrt(N)
        $maxDistance = sqrt($dimensionsCount);

        // Compliance = 1 - (Distance / MaxDistance)
        // If distance is 0, compliance is 1.
        // If distance is max, compliance is 0.
        $compliance = 1.0 - ($distance / $maxDistance);

        return max(0.0, min(1.0, $compliance));
    }

    private function getFactionAxisValue(IdeologyVector $ideology, string $axis): float
    {
        return match ($axis) {
            'militarism' => $ideology->militarism,
            'spiritualism' => $ideology->spiritualism,
            'expansionism' => $ideology->expansionism,
            'collectivism' => $ideology->collectivism,
            'purity' => $ideology->purity,
            default => 0.0,
        };
    }
}
