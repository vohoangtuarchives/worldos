<?php

namespace Tuzy\Application\Material\Engine;

use Illuminate\Support\Collection;

/**
 * MemoryFeedbackLoop - Component 7 of MaterialLawEngine
 * 
 * Purpose: Past distorts present. Legacy affects activation and state.
 * Mechanisms: Legacy → Activation, Legacy → State, Legacy → Mutation
 */
class MemoryFeedbackLoop
{
    /**
     * Calculate feedback effects from active legacies.
     * 
     * @param Collection $instances Material instances with historical_traces
     * @param array $pressureLevels Current pressure levels
     * @return array Feedback effects
     */
    public function calculate(Collection $instances, array $pressureLevels): array
    {
        $feedbackEffects = [];

        // Collect all active legacies
        $legacies = $this->collectLegacies($instances);

        // 1. Legacy → Activation feedback
        $feedbackEffects = array_merge(
            $feedbackEffects,
            $this->legacyToActivation($legacies, $pressureLevels)
        );

        // 2. Legacy → State feedback
        $feedbackEffects = array_merge(
            $feedbackEffects,
            $this->legacyToState($legacies, $pressureLevels)
        );

        // 3. Legacy → Mutation feedback
        $feedbackEffects = array_merge(
            $feedbackEffects,
            $this->legacyToMutation($legacies)
        );

        return $feedbackEffects;
    }

    /**
     * Collect all legacy traces from instances.
     */
    private function collectLegacies(Collection $instances): array
    {
        $legacies = [];

        foreach ($instances as $instance) {
            $traces = $instance->historical_traces ?? [];
            
            foreach ($traces as $trace) {
                $legacies[] = $trace;
            }
        }

        return $legacies;
    }

    /**
     * Legacy affects material activation thresholds.
     */
    private function legacyToActivation(array $legacies, array $pressures): array
    {
        $effects = [];

        foreach ($legacies as $legacy) {
            $legacyCode = $legacy['legacy_code'] ?? null;
            $strength = $legacy['strength'] ?? 0;

            if (!$legacyCode || $strength < 0.3) {
                continue; // Weak legacies don't affect activation
            }

            // Hunger taboo → Ritualized Remembrance
            if ($legacyCode === 'HUNGER_TABOO') {
                $effects[] = [
                    'legacy' => $legacyCode,
                    'affects' => 'RITUALIZED_REMEMBRANCE',
                    'type' => 'activation_boost',
                    'delta' => $strength * 0.5,
                    'description' => 'Famine memory strengthens ritual remembrance',
                ];
            }

            // Dark age marker → Tech Mythologization
            if ($legacyCode === 'DARK_AGE_MARKER') {
                $effects[] = [
                    'legacy' => $legacyCode,
                    'affects' => 'TECH_MYTHOLOGIZATION',
                    'type' => 'activation_boost',
                    'delta' => $strength * 0.6,
                    'description' => 'Collapse memory creates tech mythologization',
                ];
            }

            // Generational trauma → Grievance Accumulation
            if ($legacyCode === 'GENERATIONAL_TRAUMA') {
                $effects[] = [
                    'legacy' => $legacyCode,
                    'affects' => 'GRIEVANCE_ACCUMULATION',
                    'type' => 'activation_boost',
                    'delta' => $strength * 0.7,
                    'description' => 'Trauma passes through generations as grievance',
                ];
            }
        }

        return $effects;
    }

    /**
     * Legacy affects current pressure levels.
     */
    private function legacyToState(array $legacies, array $pressures): array
    {
        $effects = [];

        foreach ($legacies as $legacy) {
            $legacyCode = $legacy['legacy_code'] ?? null;
            $strength = $legacy['strength'] ?? 0;

            if (!$legacyCode || $strength < 0.4) {
                continue;
            }

            // Class resentment → Inequality pressure
            if ($legacyCode === 'CLASS_RESENTMENT') {
                $effects[] = [
                    'legacy' => $legacyCode,
                    'affects' => 'inequality',
                    'type' => 'pressure_amplification',
                    'delta' => $strength * 0.3,
                    'description' => 'Historical class resentment amplifies current inequality',
                ];
            }

            // Lost knowledge myth → Innovation friction
            if ($legacyCode === 'LOST_KNOWLEDGE_MYTH') {
                $effects[] = [
                    'legacy' => $legacyCode,
                    'affects' => 'innovation_friction',
                    'type' => 'pressure_amplification',
                    'delta' => $strength * 0.4,
                    'description' => '"Ancients were better" myth increases innovation resistance',
                ];
            }
        }

        return $effects;
    }

    /**
     * Legacy shapes material mutations.
     */
    private function legacyToMutation(array $legacies): array
    {
        $effects = [];

        foreach ($legacies as $legacy) {
            $legacyCode = $legacy['legacy_code'] ?? null;
            $strength = $legacy['strength'] ?? 0;

            if (!$legacyCode || $strength < 0.5) {
                continue;
            }

            // Hunger taboo → Subsistence base mutation
            if ($legacyCode === 'HUNGER_TABOO') {
                $effects[] = [
                    'legacy' => $legacyCode,
                    'affects' => 'SUBSISTENCE_BASE',
                    'type' => 'mutation_bias',
                    'direction' => 'conservative',
                    'description' => 'Famine memory creates conservative subsistence strategies',
                ];
            }

            // Dark age marker → Infrastructure mutation
            if ($legacyCode === 'DARK_AGE_MARKER') {
                $effects[] = [
                    'legacy' => $legacyCode,
                    'affects' => 'PRODUCTION_INFRASTRUCTURE',
                    'type' => 'mutation_bias',
                    'direction' => 'decentralized',
                    'description' => 'Collapse memory favors decentralized infrastructure',
                ];
            }
        }

        return $effects;
    }
}
