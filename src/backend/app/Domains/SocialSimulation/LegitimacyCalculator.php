<?php

namespace App\Domains\SocialSimulation;

use App\Domains\CognitiveKernel\ArchetypeWeight;
use App\Domains\CognitiveKernel\CouplingRules;
use App\Models\World;
use Illuminate\Support\Collection;
use WorldOS\Society\SocialSimulation\ValueObject\LegitimacyResult;

/**
 * Legitimacy Calculator
 * 
 * Calculates social legitimacy based on archetype-economy-power coupling.
 * 
 * Core Formula (from ARCHETYPE_ECONOMY_POWER_COUPLING.md):
 * legitimacy = f(archetype_weight, myth_intensity) - economic_inequality - trauma_memory
 * 
 * Constitutional Constraint:
 * This formula is part of Cognitive Kernel and immutable per major version
 */
class LegitimacyCalculator
{
    private CouplingRules $couplingRules;

    public function __construct(?string $kernelVersion = null)
    {
        $this->couplingRules = new CouplingRules($kernelVersion);
    }

    /**
     * Calculate overall world legitimacy
     * 
     * @return LegitimacyResult
     */
    public function calculate(World $world): LegitimacyResult
    {
        // Get archetype weights
        $archetypeWeights = ArchetypeWeight::where('world_id', $world->id)->get();

        // Calculate components
        $archetypeContribution = $this->calculateArchetypeContribution($world, $archetypeWeights);
        $mythIntensity = $this->calculateMythIntensity($world);
        $economicInequality = $this->calculateEconomicInequality($world);
        $traumaMemory = $this->calculateTraumaMemory($world);

        // Apply formula
        $legitimacy = ($archetypeContribution * $mythIntensity) 
                     - $economicInequality 
                     - $traumaMemory;

        // Clamp to 0-1
        $legitimacy = max(0, min(1, $legitimacy));

        return new LegitimacyResult(
            legitimacy: $legitimacy,
            components: [
                'archetype_contribution' => $archetypeContribution,
                'myth_intensity' => $mythIntensity,
                'economic_inequality' => $economicInequality,
                'trauma_memory' => $traumaMemory,
            ],
            thresholdStatus: $this->getThresholdStatus($legitimacy)
        );
    }

    /**
     * Calculate archetype contribution to legitimacy
     */
    private function calculateArchetypeContribution(
        World $world,
        Collection $archetypeWeights
    ): float {
        // Weight active archetypes more heavily
        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($archetypeWeights as $weight) {
            $effectiveWeight = $weight->effective_weight ?? $weight->weight;
            $weightedSum += $effectiveWeight;
            $totalWeight += 1;
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0.5;
    }

    /**
     * Calculate myth intensity (average strength of active myths)
     */
    private function calculateMythIntensity(World $world): float
    {
        $avgStrength = $world->myths()
            ->where('strength', '>', 0.1)
            ->avg('strength');

        return $avgStrength ?? 0.5;
    }

    /**
     * Calculate economic inequality
     * 
     * Higher inequality reduces legitimacy
     */
    private function calculateEconomicInequality(World $world): float
    {
        // Check if factions exist (proxy for economic stratification)
        $factions = $world->factions;

        if ($factions->isEmpty()) {
            return 0.1; // Baseline low inequality
        }

        // Calculate power/resource disparity between factions
        $powerLevels = $factions->pluck('power_level')->filter();
        
        if ($powerLevels->isEmpty()) {
            return 0.1;
        }

        $maxPower = $powerLevels->max();
        $minPower = $powerLevels->min();
        $inequality = $maxPower - $minPower;

        return min(0.5, $inequality); // Cap at 0.5
    }

    /**
     * Calculate trauma memory impact
     * 
     * Unresolved scars reduce legitimacy
     */
    private function calculateTraumaMemory(World $world): float
    {
        $scarsCount = $world->scars()->count();
        $totalMyths = $world->myths()->count();

        if ($totalMyths === 0) {
            return 0;
        }

        // Scar-to-myth ratio indicates unresolved trauma
        $traumaRatio = $scarsCount / max(1, $totalMyths);

        return min(0.4, $traumaRatio * 0.5); // Cap at 0.4
    }

    /**
     * Get threshold status for legitimacy
     */
    private function getThresholdStatus(float $legitimacy): array
    {
        return [
            'stable' => $legitimacy > 0.7,
            'unstable' => $legitimacy > 0.4 && $legitimacy <= 0.7,
            'crisis' => $legitimacy > 0.2 && $legitimacy <= 0.4,
            'collapse' => $legitimacy <= 0.2,
            'current' => match(true) {
                $legitimacy > 0.7 => 'stable',
                $legitimacy > 0.4 => 'unstable',
                $legitimacy > 0.2 => 'crisis',
                default => 'collapse'
            }
        ];
    }

    /**
     * Calculate legitimacy for a specific action/policy
     */
    public function calculateForAction(
        World $world,
        string $actionType,
        array $actionContext
    ): float {
        $baseLegitimacy = $this->calculate($world)->legitimacy;

        // Modify based on action type and archetype alignment
        $archetypeModifier = $this->getArchetypeModifierForAction(
            $world,
            $actionType,
            $actionContext
        );

        return max(0, min(1, $baseLegitimacy + $archetypeModifier));
    }

    /**
     * Get archetype modifier for specific action
     */
    private function getArchetypeModifierForAction(
        World $world,
        string $actionType,
        array $actionContext
    ): float {
        // Get archetypes that support or oppose this action type
        $supportingArchetypes = $actionContext['supporting_archetypes'] ?? [];
        $opposingArchetypes = $actionContext['opposing_archetypes'] ?? [];

        $modifier = 0;

        foreach ($supportingArchetypes as $archetypeKey) {
            $weight = ArchetypeWeight::where('world_id', $world->id)
                ->where('archetype_key', $archetypeKey)
                ->first();
            
            if ($weight) {
                $modifier += $weight->weight * 0.1;
            }
        }

        foreach ($opposingArchetypes as $archetypeKey) {
            $weight = ArchetypeWeight::where('world_id', $world->id)
                ->where('archetype_key', $archetypeKey)
                ->first();
            
            if ($weight) {
                $modifier -= $weight->weight * 0.1;
            }
        }

        return $modifier;
    }
}
