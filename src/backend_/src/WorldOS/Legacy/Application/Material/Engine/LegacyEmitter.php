<?php

namespace WorldOS\Legacy\Application\Material\Engine;

use WorldOS\Legacy\Domain\Material\MaterialInstance;

/**
 * LegacyEmitter - Component 6 of MaterialLawEngine (Enhanced)
 * 
 * Purpose: Emit traces when materials die or weaken significantly.
 * Rule: MANDATORY for all materials. Legacy affects future activation.
 */
class LegacyEmitter
{
    /**
     * Emit legacy traces from a material instance.
     * 
     * @param MaterialInstance $instance
     * @param string|null $retirementReason 'decay' | 'conflict' | 'collapse'
     * @return array Legacy traces
     */
    public function emitTraces(MaterialInstance $instance, ?string $retirementReason = null): array
    {
        $material = $instance->material;
        $traces = [];

        // 1. Retirement legacy (mandatory)
        if ($instance->retired_at) {
            $traces[] = $this->createRetirementLegacy($instance, $retirementReason);
        }

        // 2. High-strength legacy (living legacy)
        if (!$instance->retired_at && $instance->strength_level > 8) {
            $traces[] = $this->createLivingLegacy($instance);
        }

        // 3. Domain-specific legacy
        $domainLegacy = $this->createDomainLegacy($instance);
        if ($domainLegacy) {
            $traces[] = $domainLegacy;
        }

        return array_filter($traces);
    }

    /**
     * Create legacy from retired material.
     */
    private function createRetirementLegacy(MaterialInstance $instance, ?string $reason): array
    {
        $material = $instance->material;
        $strength = $this->calculateLegacyStrength($instance, $reason);

        return [
            'legacy_code' => $this->deriveLegacyCode($material->code),
            'origin_material' => $material->code,
            'strength' => $strength,
            'type' => 'retirement',
            'reason' => $reason ?? 'natural_decay',
            'epoch' => $instance->retired_at?->timestamp ?? now()->timestamp,
            'description' => $this->generateLegacyDescription($material->code, 'retirement'),
        ];
    }

    /**
     * Create legacy from high-strength active material.
     */
    private function createLivingLegacy(MaterialInstance $instance): array
    {
        $material = $instance->material;
        $strength = min(0.8, $instance->strength_level / 10.0);

        return [
            'legacy_code' => $this->deriveLegacyCode($material->code) . '_NORM',
            'origin_material' => $material->code,
            'strength' => $strength,
            'type' => 'cultural_norm',
            'epoch' => now()->timestamp,
            'description' => $this->generateLegacyDescription($material->code, 'living'),
        ];
    }

    /**
     * Create domain-specific legacy.
     */
    private function createDomainLegacy(MaterialInstance $instance): ?array
    {
        $material = $instance->material;

        // Economy domain legacies
        if ($material->code === 'FAMINE_TRIGGER' && $instance->retired_at) {
            return [
                'legacy_code' => 'HUNGER_TABOO',
                'origin_material' => 'FAMINE_TRIGGER',
                'strength' => 0.7,
                'type' => 'trauma_memory',
                'description' => 'Collective memory of famine creates food taboos and hoarding behavior',
            ];
        }

        if ($material->code === 'INEQUALITY_GRADIENT' && $instance->strength_level > 7) {
            return [
                'legacy_code' => 'CLASS_RESENTMENT',
                'origin_material' => 'INEQUALITY_GRADIENT',
                'strength' => 0.6,
                'type' => 'social_memory',
                'description' => 'Deep inequality leaves lasting class resentment',
            ];
        }

        // Technology domain legacies
        if ($material->code === 'INFRASTRUCTURE_COLLAPSE_TRIGGER' && $instance->retired_at) {
            return [
                'legacy_code' => 'DARK_AGE_MARKER',
                'origin_material' => 'INFRASTRUCTURE_COLLAPSE_TRIGGER',
                'strength' => 0.9,
                'type' => 'historical_marker',
                'description' => 'Infrastructure collapse marks beginning of dark age',
            ];
        }

        if ($material->code === 'SKILL_ATTRITION' && $instance->retired_at) {
            return [
                'legacy_code' => 'LOST_KNOWLEDGE_MYTH',
                'origin_material' => 'SKILL_ATTRITION',
                'strength' => 0.7,
                'type' => 'mythologization',
                'description' => 'Lost skills become mythical craftsmanship',
            ];
        }

        // Memory domain legacies
        if ($material->code === 'TRAUMA_ENCODING' && $instance->strength_level > 7) {
            return [
                'legacy_code' => 'GENERATIONAL_TRAUMA',
                'origin_material' => 'TRAUMA_ENCODING',
                'strength' => 0.8,
                'type' => 'deep_memory',
                'description' => 'Trauma passes through generations',
            ];
        }

        return null;
    }

    /**
     * Calculate legacy strength based on instance and retirement reason.
     */
    private function calculateLegacyStrength(MaterialInstance $instance, ?string $reason): float
    {
        $baseStrength = $instance->strength_level / 10.0;

        $multiplier = match($reason) {
            'explosion' => 1.5,  // Violent end → strong legacy
            'collapse' => 1.3,   // Catastrophic → strong legacy
            'conflict' => 1.2,   // Conflict → moderate legacy
            'decay' => 0.8,      // Natural decay → weaker legacy
            default => 1.0,
        };

        return min(1.0, $baseStrength * $multiplier);
    }

    /**
     * Derive legacy code from material code.
     */
    private function deriveLegacyCode(string $materialCode): string
    {
        return $materialCode . '_LEGACY';
    }

    /**
     * Generate human-readable legacy description.
     */
    private function generateLegacyDescription(string $materialCode, string $type): string
    {
        if ($type === 'retirement') {
            return "Historical trace of {$materialCode} after collapse";
        }

        return "Cultural norm deeply ingrained from {$materialCode}";
    }
}
