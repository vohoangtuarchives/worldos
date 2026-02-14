<?php

namespace App\Domains\SocialSimulation;

use App\Domains\CognitiveKernel\ArchetypeWeight;
use App\Models\World;
use Illuminate\Support\Collection;

/**
 * Perception Filter
 * 
 * Archetypes bias how society perceives and legitimizes actions.
 * 
 * Key Insight (from ARCHETYPE_ECONOMY_POWER_COUPLING.md):
 * "Archetype doesn't create actions. It creates how society accepts or justifies actions."
 * 
 * Example:
 * - Economy bad + "sacrifice" archetype strong = people endure
 * - Economy bad + "rebellion" archetype strong = uprising
 */
class PerceptionFilter
{
    /**
     * Filter economic action through archetype lens
     * 
     * Returns modified perception of the action's legitimacy
     */
    public function filterEconomicAction(
        World $world,
        array $economicAction
    ): array {
        $actionType = $economicAction['type'] ?? 'unknown';
        $severity = $economicAction['severity'] ?? 0.5;

        // Get active archetypes
        $archetypeWeights = ArchetypeWeight::where('world_id', $world->id)
            ->where('weight', '>', 0.3)
            ->get();

        // Calculate perception shifts
        $perceptionShifts = $this->calculatePerceptionShifts(
            $actionType,
            $severity,
            $archetypeWeights
        );

        return [
            'action' => $economicAction,
            'base_severity' => $severity,
            'perceived_severity' => $severity + $perceptionShifts['severity_modifier'],
            'legitimacy_modifier' => $perceptionShifts['legitimacy_modifier'],
            'narrative' => $perceptionShifts['narrative'],
            'dominant_archetype' => $perceptionShifts['dominant_archetype'],
        ];
    }

    /**
     * Filter power action through archetype lens
     */
    public function filterPowerAction(
        World $world,
        array $powerAction
    ): array {
        $actionType = $powerAction['type'] ?? 'unknown';
        $coercion = $powerAction['coercion'] ?? 0.5;

        $archetypeWeights = ArchetypeWeight::where('world_id', $world->id)
            ->where('weight', '>', 0.3)
            ->get();

        $perceptionShifts = $this->calculatePowerPerception(
            $actionType,
            $coercion,
            $archetypeWeights
        );

        return [
            'action' => $powerAction,
            'base_coercion' => $coercion,
            'perceived_coercion' => $coercion + $perceptionShifts['coercion_modifier'],
            'legitimacy_modifier' => $perceptionShifts['legitimacy_modifier'],
            'narrative' => $perceptionShifts['narrative'],
            'dominant_archetype' => $perceptionShifts['dominant_archetype'],
        ];
    }

    /**
     * Calculate how archetypes shift perception of economic actions
     */
    private function calculatePerceptionShifts(
        string $actionType,
        float $severity,
        Collection $archetypeWeights
    ): array {
        $shifts = [
            'severity_modifier' => 0,
            'legitimacy_modifier' => 0,
            'narrative' => '',
            'dominant_archetype' => null,
        ];

        // Find dominant archetype
        $dominant = $archetypeWeights->sortByDesc('weight')->first();
        
        if (!$dominant) {
            return $shifts;
        }

        $archetypeKey = $dominant->archetype_key;
        $shifts['dominant_archetype'] = $archetypeKey;

        // Apply archetype-specific perception shifts
        $shifts = match($archetypeKey) {
            'sacrifice' => $this->applySacrificeFilter($actionType, $severity, $shifts),
            'domination' => $this->applyDominationFilter($actionType, $severity, $shifts),
            'equality' => $this->applyEqualityFilter($actionType, $severity, $shifts),
            'hierarchy' => $this->applyHierarchyFilter($actionType, $severity, $shifts),
            'rebellion' => $this->applyRebellionFilter($actionType, $severity, $shifts),
            'submission' => $this->applySubmissionFilter($actionType, $severity, $shifts),
            default => $shifts,
        };

        return $shifts;
    }

    /**
     * Sacrifice archetype filter: Pain is noble
     */
    private function applySacrificeFilter(string $actionType, float $severity, array $shifts): array
    {
        if (in_array($actionType, ['scarcity', 'taxation', 'labor_extraction'])) {
            $shifts['severity_modifier'] = -0.2; // Pain feels less severe
            $shifts['legitimacy_modifier'] = 0.1; // Legitimized as necessary sacrifice
            $shifts['narrative'] = 'This hardship is our sacred duty';
        }
        return $shifts;
    }

    /**
     * Domination archetype filter: Strength justifies
     */
    private function applyDominationFilter(string $actionType, float $severity, array $shifts): array
    {
        if (in_array($actionType, ['coercion', 'monopoly', 'extraction'])) {
            $shifts['legitimacy_modifier'] = 0.15; // Strong taking from weak is natural
            $shifts['narrative'] = 'Power commands, weakness obeys';
        }
        return $shifts;
    }

    /**
     * Equality archetype filter: Fairness demanded
     */
    private function applyEqualityFilter(string $actionType, float $severity, array $shifts): array
    {
        if (in_array($actionType, ['inequality', 'privilege', 'extraction'])) {
            $shifts['severity_modifier'] = 0.3; // Unfairness feels worse
            $shifts['legitimacy_modifier'] = -0.2; // Strongly delegitimized
            $shifts['narrative'] = 'This injustice cannot stand';
        }
        return $shifts;
    }

    /**
     * Hierarchy archetype filter: Order above fairness
     */
    private function applyHierarchyFilter(string $actionType, float $severity, array $shifts): array
    {
        if (in_array($actionType, ['inequality', 'privilege'])) {
            $shifts['severity_modifier'] = -0.1; // Expected, less shocking
            $shifts['legitimacy_modifier'] = 0.1; // Natural order
            $shifts['narrative'] = 'Each must know their place';
        }
        return $shifts;
    }

    /**
     * Rebellion archetype filter: Resistance is virtue
     */
    private function applyRebellionFilter(string $actionType, float $severity, array $shifts): array
    {
        if (in_array($actionType, ['coercion', 'oppression', 'extraction'])) {
            $shifts['severity_modifier'] = 0.2; // Feels more oppressive
            $shifts['legitimacy_modifier'] = -0.25; // Strongly rejected
            $shifts['narrative'] = 'We will not be silenced';
        }
        return $shifts;
    }

    /**
     * Submission archetype filter: Obedience is safety
     */
    private function applySubmissionFilter(string $actionType, float $severity, array $shifts): array
    {
        if (in_array($actionType, ['coercion', 'oppression'])) {
            $shifts['severity_modifier'] = -0.15; // Accept as inevitable
            $shifts['legitimacy_modifier'] = 0.05; // Weak legitimization
            $shifts['narrative'] = 'It is not our place to question';
        }
        return $shifts;
    }

    /**
     * Calculate power perception shifts
     */
    private function calculatePowerPerception(
        string $actionType,
        float $coercion,
        Collection $archetypeWeights
    ): array {
        // Similar to economic perception but for power actions
        return $this->calculatePerceptionShifts($actionType, $coercion, $archetypeWeights);
    }
}
