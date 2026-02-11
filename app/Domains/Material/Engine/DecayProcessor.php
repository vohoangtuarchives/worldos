<?php

namespace App\Domains\Material\Engine;

use App\Domains\Material\MaterialInstance;
use App\Domains\Material\Enums\MaterialOntology;

/**
 * DecayProcessor - Component 5 of MaterialLawEngine (Enhanced)
 * 
 * Purpose: Apply entropy to all materials. Nothing lasts forever.
 * Decay Types: Time-based, Condition-based, Shock-accelerated
 */
class DecayProcessor
{
    /**
     * Process decay for a material instance.
     * 
     * @param MaterialInstance $instance
     * @param array $worldState Current world pressures
     * @return array Decay result
     */
    public function processDecay(MaterialInstance $instance, array $worldState = []): array
    {
        $material = $instance->material;
        $decayAmount = 0;

        // 1. Time-based decay (baseline)
        $decayAmount += $this->calculateTimeDecay($instance);

        // 2. Condition-based decay
        $decayAmount += $this->calculateConditionDecay($instance, $worldState);

        // 3. Shock-accelerated decay
        $decayAmount += $this->calculateShockDecay($instance, $worldState);

        // Apply decay
        $instance->degradation_level += $decayAmount;

        // Check for retirement
        $retired = false;
        if ($instance->degradation_level >= 100 && !$instance->retired_at) {
            $instance->retired_at = now();
            $retired = true;
        }

        return [
            'material_code' => $material->code,
            'decay_amount' => $decayAmount,
            'new_degradation' => $instance->degradation_level,
            'new_strength' => $instance->strength_level,
            'retired' => $retired,
        ];
    }

    /**
     * Time-based decay (constant entropy).
     */
    private function calculateTimeDecay(MaterialInstance $instance): float
    {
        $material = $instance->material;

        return match($material->ontology) {
            MaterialOntology::INSTITUTIONAL => $instance->strength_level < 3 ? 2.0 : 1.0,
            MaterialOntology::SYMBOLIC => $instance->strength_level < 1 ? 1.0 : 0.5,
            MaterialOntology::BEHAVIORAL => 1.5,
            default => 1.0,
        };
    }

    /**
     * Condition-based decay (decay when specific conditions met).
     */
    private function calculateConditionDecay(MaterialInstance $instance, array $worldState): float
    {
        $material = $instance->material;
        $decay = 0;

        // Infrastructure decays faster without maintenance
        if (str_contains($material->code, 'INFRASTRUCTURE') || str_contains($material->code, 'PRODUCTION')) {
            $maintenanceBurden = $worldState['maintenance_burden'] ?? 0.5;
            if ($maintenanceBurden > 0.7) {
                $decay += 2.0; // High maintenance burden accelerates decay
            }
        }

        // Memory materials decay when not reinforced
        if (in_array($material->code, ['CANONICAL_HISTORY', 'RITUALIZED_REMEMBRANCE', 'ARTIFACT_ANCHORING'])) {
            $institutionalEducation = $worldState['institutional_education'] ?? 0.5;
            if ($institutionalEducation < 0.3) {
                $decay += 1.5; // Weak education → memory decay
            }
        }

        // Tech materials decay without knowledge preservation
        if (in_array($material->code, ['TECHNICAL_LITERACY', 'KNOWLEDGE_PRESERVATION'])) {
            $preservationLevel = $worldState['knowledge_preservation'] ?? 0.5;
            if ($preservationLevel < 0.4) {
                $decay += 2.0; // Knowledge loss accelerates
            }
        }

        return $decay;
    }

    /**
     * Shock-accelerated decay (rapid decay on catastrophic events).
     */
    private function calculateShockDecay(MaterialInstance $instance, array $worldState): float
    {
        $decay = 0;

        // Infrastructure collapse shock
        if (($worldState['infrastructure_collapse'] ?? false)) {
            $decay += 5.0; // Catastrophic decay
        }

        // Famine shock
        if (($worldState['famine_active'] ?? false)) {
            if (str_contains($instance->material->code, 'SUBSISTENCE') || 
                str_contains($instance->material->code, 'PRODUCTION')) {
                $decay += 3.0;
            }
        }

        // Invasion shock
        if (($worldState['invasion_active'] ?? false)) {
            $decay += 2.0; // General disruption
        }

        return $decay;
    }
}
