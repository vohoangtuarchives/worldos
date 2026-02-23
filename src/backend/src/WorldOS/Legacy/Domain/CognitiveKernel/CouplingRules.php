<?php

namespace WorldOS\Legacy\Domain\CognitiveKernel;

use App\Models\World;
use App\Models\WorldLaw;

/**
 * Coupling Rules Service
 * 
 * Manages the coupling between:
 * - Archetypes ↔ Economy
 * - Archetypes ↔ Power
 * - Archetypes ↔ World Laws
 * 
 * Constitutional Constraint (ADR-1001):
 * These rules are immutable per major kernel version
 */
class CouplingRules
{
    private KernelVersion $kernelVersion;

    public function __construct(?string $version = null)
    {
        $this->kernelVersion = $version 
            ? KernelVersion::findByVersion($version) 
            : KernelVersion::current();
    }

    /**
     * Calculate legitimacy for a world state
     * 
     * Formula: legitimacy = Σ(archetype_weight * myth_intensity) - inequality - trauma
     */
    public function calculateLegitimacy(
        World $world,
        array $archetypeWeights,
        float $mythIntensity,
        float $inequality = 0,
        float $trauma = 0
    ): float {
        $archetypeContribution = 0;

        foreach ($archetypeWeights as $weight) {
            $archetypeContribution += $weight->effective_weight * $mythIntensity;
        }

        $legitimacy = $archetypeContribution - $inequality - $trauma;

        return max(0, min(1, $legitimacy));
    }

    /**
     * Get archetype coupling for a world law
     */
    public function getArchetypeCouplingForLaw(WorldLaw $law): ?array
    {
        return $law->archetype_coupling;
    }

    /**
     * Check if a law is legitimate given current archetype state
     */
    public function isLawLegitimate(
        WorldLaw $law,
        array $archetypeWeights
    ): bool {
        $coupling = $law->archetype_coupling;
        
        if (!$coupling) {
            return true; // No coupling requirement
        }

        // Check if required archetypes are active
        $requiredArchetypes = $coupling['required_archetypes'] ?? [];
        
        foreach ($requiredArchetypes as $archetypeKey => $minWeight) {
            $weight = collect($archetypeWeights)
                ->firstWhere('archetype_key', $archetypeKey);
            
            if (!$weight || $weight->weight < $minWeight) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get legitimacy weight modifier based on archetype state
     */
    public function getLegitimacyModifier(
        WorldLaw $law,
        array $archetypeWeights
    ): float {
        $coupling = $law->archetype_coupling;
        
        if (!$coupling) {
            return 1.0;
        }

        $modifier = 1.0;
        $supportedArchetypes = $coupling['supported_by'] ?? [];

        foreach ($supportedArchetypes as $archetypeKey => $influence) {
            $weight = collect($archetypeWeights)
                ->firstWhere('archetype_key', $archetypeKey);
            
            if ($weight) {
                $modifier += ($weight->weight * $influence);
            }
        }

        return max(0, $modifier);
    }

    /**
     * Get coupling rules from kernel version
     */
    public function getRules(): array
    {
        return $this->kernelVersion->coupling_rules ?? [];
    }

    /**
     * Get drift threshold from kernel
     */
    public function getDriftThreshold(): float
    {
        $rules = $this->getRules();
        return $rules['drift_threshold'] ?? 0.1;
    }

    /**
     * Get allowed mutation triggers
     */
    public function getMutationTriggers(): array
    {
        $rules = $this->getRules();
        return $rules['mutation_triggers'] ?? [
            'EXTREME_COLLAPSE',
            'MYTH_PARADOX',
            'REPEATED_FAILURE'
        ];
    }
}
