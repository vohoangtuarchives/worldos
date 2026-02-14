<?php

namespace App\Domains\Material\Engine;

use Illuminate\Support\Collection;
use App\Domains\Material\Material;

/**
 * ActivationResolver - Component 2 of MaterialLawEngine
 * 
 * Purpose: Determine which dormant materials should activate based on thresholds.
 * Rule: Activation based on threshold, NOT random.
 */
class ActivationResolver
{
    /**
     * Resolve which dormant materials should activate.
     * 
     * @param array $pressureLevels From StateEvaluator
     * @param Collection $dormantMaterials Materials not yet instantiated or retired
     * @return array Activation decisions
     */
    public function resolve(array $pressureLevels, Collection $dormantMaterials): array
    {
        $activations = [];

        foreach ($dormantMaterials as $material) {
            $decision = $this->evaluateActivation($material, $pressureLevels);
            
            if ($decision['should_activate']) {
                $activations[] = $decision;
            }
        }

        return $activations;
    }

    /**
     * Evaluate if a single material should activate.
     */
    private function evaluateActivation(Material $material, array $pressures): array
    {
        $decision = [
            'material_code' => $material->code,
            'should_activate' => false,
            'intensity' => 0.0,
            'reason' => null,
        ];

        // Parse preconditions
        if ($material->preconditions) {
            $preconditionsMet = $this->evaluatePreconditions($material->preconditions, $pressures);
            
            if ($preconditionsMet['met']) {
                $decision['should_activate'] = true;
                $decision['intensity'] = $preconditionsMet['intensity'];
                $decision['reason'] = $preconditionsMet['reason'];
                return $decision;
            }
        }

        // Check threshold-based activation for specific materials
        $activation = $this->checkThresholds($material->code, $pressures);
        
        if ($activation) {
            $decision['should_activate'] = true;
            $decision['intensity'] = $activation['intensity'];
            $decision['reason'] = $activation['reason'];
        }

        return $decision;
    }

    /**
     * Evaluate preconditions from material definition.
     * Format: ["subsistence_base < 3", "inequality > 7"]
     */
    private function evaluatePreconditions(array $preconditions, array $pressures): array
    {
        foreach ($preconditions as $condition) {
            $result = $this->parseCondition($condition, $pressures);
            
            if ($result['met']) {
                return $result;
            }
        }

        return ['met' => false];
    }

    /**
     * Parse a single condition string.
     * Examples: "subsistence_base < 3", "inequality > 7", "collapse OR famine"
     */
    private function parseCondition(string $condition, array $pressures): array
    {
        // Handle OR conditions
        if (stripos($condition, ' OR ') !== false) {
            $parts = preg_split('/\s+OR\s+/i', $condition);
            foreach ($parts as $part) {
                $result = $this->parseCondition(trim($part), $pressures);
                if ($result['met']) {
                    return $result;
                }
            }
            return ['met' => false];
        }

        // Handle simple comparisons
        if (preg_match('/^(\w+)\s*([<>]=?)\s*([\d.]+)$/', $condition, $matches)) {
            $variable = $matches[1];
            $operator = $matches[2];
            $threshold = (float) $matches[3];

            // Convert threshold from 0-10 scale to 0-1 scale
            $normalizedThreshold = $threshold / 10.0;

            $currentValue = $pressures[$variable] ?? 0.5;

            $met = match($operator) {
                '<' => $currentValue < $normalizedThreshold,
                '>' => $currentValue > $normalizedThreshold,
                '<=' => $currentValue <= $normalizedThreshold,
                '>=' => $currentValue >= $normalizedThreshold,
                default => false,
            };

            if ($met) {
                $intensity = match($operator) {
                    '<' => 1.0 - ($currentValue / $normalizedThreshold),
                    '>' => ($currentValue - $normalizedThreshold) / (1.0 - $normalizedThreshold),
                    default => abs($currentValue - $normalizedThreshold),
                };

                return [
                    'met' => true,
                    'intensity' => min(1.0, max(0.0, $intensity)),
                    'reason' => $condition,
                ];
            }
        }

        return ['met' => false];
    }

    /**
     * Check hardcoded thresholds for specific materials.
     * This is a fallback for materials without preconditions.
     */
    private function checkThresholds(string $materialCode, array $pressures): ?array
    {
        return match($materialCode) {
            // Economy triggers
            'FAMINE_TRIGGER' => $pressures['subsistence'] < 0.3 ? [
                'intensity' => 1.0 - $pressures['subsistence'] * 3.33,
                'reason' => 'Subsistence base critically low'
            ] : null,

            'RESOURCE_CONFLICT_PRESSURE' => $pressures['subsistence'] < 0.4 && $pressures['inequality'] > 0.6 ? [
                'intensity' => (0.4 - $pressures['subsistence']) + ($pressures['inequality'] - 0.6),
                'reason' => 'Low subsistence + high inequality'
            ] : null,

            // Memory triggers
            'TRAUMA_ENCODING' => $pressures['famine_risk'] > 0.6 ? [
                'intensity' => $pressures['famine_risk'],
                'reason' => 'Famine creates collective trauma'
            ] : null,

            'GRIEVANCE_ACCUMULATION' => $pressures['inequality'] > 0.7 ? [
                'intensity' => $pressures['inequality'],
                'reason' => 'Extreme inequality fuels grievances'
            ] : null,

            'NOSTALGIA_PRESSURE' => $pressures['subsistence'] < 0.4 ? [
                'intensity' => 1.0 - $pressures['subsistence'] * 2.5,
                'reason' => 'Economic decline triggers nostalgia'
            ] : null,

            // Technology triggers
            'INFRASTRUCTURE_COLLAPSE_TRIGGER' => $pressures['infrastructure_integrity'] < 0.2 ? [
                'intensity' => 1.0 - $pressures['infrastructure_integrity'] * 5,
                'reason' => 'Infrastructure critically degraded'
            ] : null,

            'SKILL_ATTRITION' => $pressures['tech_decay'] > 0.7 ? [
                'intensity' => $pressures['tech_decay'],
                'reason' => 'Knowledge preservation failing'
            ] : null,

            // Interaction triggers
            'MIGRATION_PRESSURE' => $pressures['famine_risk'] > 0.6 || $pressures['external_threat'] > 0.7 ? [
                'intensity' => max($pressures['famine_risk'], $pressures['external_threat']),
                'reason' => 'Famine or threat drives migration'
            ] : null,

            default => null,
        };
    }
}
