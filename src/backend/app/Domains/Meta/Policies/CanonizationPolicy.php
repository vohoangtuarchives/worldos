<?php

namespace App\Domains\Meta\Policies;

use App\Models\World;

class CanonizationPolicy
{
    // Thresholds for canonization
    private float $minSurvivalGenerations = 10;
    private float $minMythIntensity = 0.8;
    private float $minSacredActions = 5; // e.g., miracles, prophecies fulfilled

    /**
     * Check if a World's archetype qualifies for Sacralization (Canonization)
     */
    public function shouldCanonize(World $world, array $mythProfile): bool
    {
        // 1. Must be old enough (generation count)
        if (($world->generation ?? 0) < $this->minSurvivalGenerations) {
            return false;
        }

        // 2. Must have high myth intensity
        $intensity = $mythProfile['intensity'] ?? 0.0;
        if ($intensity < $this->minMythIntensity) {
            return false;
        }

        // 3. Must not already be a prophet/sacred origin
        if ($world->is_prophet) {
            return false; // Already sacred
        }

        return true;
    }

    /**
     * Calculate the "Voice Strength" of a new Sacred Archetype
     */
    public function calculateSacredStrength(World $world, array $mythProfile): float
    {
        $base = 0.5;
        $intensityBonus = ($mythProfile['intensity'] ?? 0.0) * 0.3;
        $ageBonus = min(0.2, ($world->generation ?? 0) * 0.01);
        
        return min(1.0, $base + $intensityBonus + $ageBonus);
    }
}
