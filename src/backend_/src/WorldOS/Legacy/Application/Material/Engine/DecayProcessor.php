<?php

namespace WorldOS\Legacy\Application\Material\Engine;

use WorldOS\Legacy\Domain\Material\MaterialInstance;
use WorldOS\Legacy\Domain\Material\Enums\MaterialOntology;

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
     * @param float $deltaTime Time passed in this tick (in years)
     * @return array Decay result
     */
    public function processDecay(MaterialInstance $instance, array $worldState = [], float $deltaTime = 1.0): array
    {
        $material = $instance->material;
        $decayAmount = 0;

        // 1. Time-based decay (baseline) - Scaled by deltaTime
        $decayAmount += $this->calculateTimeDecay($instance, $deltaTime);

        // 2. Condition-based decay - Scaled by deltaTime
        $decayAmount += $this->calculateConditionDecay($instance, $worldState) * $deltaTime;

        // 3. Shock-accelerated decay - NOT scaled (instant impact)
        // Shocks happen "during" the tick, regardless of length, typically.
        // OR: Should shocks be scaled? A 1-day war is less damaging than a 1-year war.
        // DECISION: Scale shocks too, assuming the state flag persists for the duration.
        $decayAmount += $this->calculateShockDecay($instance, $worldState) * $deltaTime;

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
    private function calculateTimeDecay(MaterialInstance $instance, float $deltaTime): float
    {
        $material = $instance->material;

        $baseRate = match($material->ontology) {
            MaterialOntology::INSTITUTIONAL => $instance->strength_level < 3 ? 2.0 : 1.0,
            MaterialOntology::SYMBOLIC => $instance->strength_level < 1 ? 1.0 : 0.5,
            MaterialOntology::BEHAVIORAL => 1.5,
            default => 1.0,
        };

        return $baseRate * $deltaTime;
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
