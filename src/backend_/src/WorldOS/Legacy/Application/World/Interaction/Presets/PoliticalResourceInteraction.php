<?php

namespace WorldOS\Legacy\Application\World\Interaction\Presets;

use WorldOS\Blueprint\Domain\Legacy\WorldState;

class PoliticalResourceInteraction implements PresetInteraction
{
    public function applyInteraction(WorldState $political, WorldState $resource): void
    {
        $interactionStrength = $this->calculateInteractionStrength($political, $resource);
        
        // Political narratives control resource flow
        $resource->resourceFlow *= (1 + $political->propagandaEffort * 0.3 * $interactionStrength);
        $political->trust *= (1 - $resource->scarcityRate * 0.2 * $interactionStrength);
        
        // War for resources escalation
        if ($resource->scarcityRate > 0.8 && $political->warProbability > 0.6) {
            $political->escalationLevel += 0.25 * $interactionStrength;
            $resource->depletionRate += 0.3 * $interactionStrength;
            
            // Spawn conflict event
            $this->spawnResourceConflict($political, $resource, $interactionStrength);
        }
        
        // Resource wealth affects political stability
        if ($resource->resourceFlow > 0.7) {
            $political->stability += 0.15 * $interactionStrength;
            $political->corruptionLevel += 0.1 * $interactionStrength;
        }
        
        // Political power affects resource distribution
        if ($political->dominanceLevel > 0.8) {
            $resource->resourceInequality += 0.2 * $interactionStrength;
            $resource->socialUnrest += 0.15 * $interactionStrength;
        }
        
        $this->applyCrossPresetEffects($political, $resource, $interactionStrength);
    }

    public function getInteractionType(): string
    {
        return 'RESOURCE_CROSSFLOW';
    }

    public function calculateCompatibility(WorldState $worldA, WorldState $worldB): float
    {
        $political = ($worldA->currentPreset === 'political') ? $worldA : $worldB;
        $resource = ($worldA->currentPreset === 'resource') ? $worldA : $worldB;
        
        // High compatibility when political power needs resources
        // and resource abundance needs political control
        $politicalNeed = max(0.8 - $resource->resourceFlow, 0);
        $resourceNeed = max($political->warProbability - 0.4, 0);
        
        $controlMatch = min($political->dominanceLevel, $resource->resourceFlow);
        $conflictPenalty = max($political->corruptionLevel - 0.5, 0) * 0.3;
        
        return ($politicalNeed + $resourceNeed + $controlMatch - $conflictPenalty) / 2;
    }

    public function canHybridize(WorldState $worldA, WorldState $worldB): bool
    {
        $strength = $this->calculateInteractionStrength($worldA, $worldB);
        $compatibility = $this->calculateCompatibility($worldA, $worldB);
        
        return $strength > 0.6 && $compatibility > 0.7;
    }

    private function calculateInteractionStrength(WorldState $political, WorldState $resource): float
    {
        return (
            $political->dominanceLevel * $resource->permeability +
            $resource->resourceFlow * $political->permeability
        ) * (1 - abs($political->trust - $resource->scarcityRate) * 0.2);
    }

    private function spawnResourceConflict(WorldState $political, WorldState $resource, float $strength): void
    {
        $conflict = [
            'type' => 'RESOURCE_WAR',
            'political_world' => $political->id,
            'resource_world' => $resource->id,
            'intensity' => $strength,
            'duration' => rand(10, 30), // ticks
            'casualty_rate' => $strength * 0.1,
            'resource_cost' => $strength * 0.2
        ];
        
        $political->activeConflicts[] = $conflict;
        $resource->activeConflicts[] = $conflict;
    }

    private function applyCrossPresetEffects(WorldState $political, WorldState $resource, float $strength): void
    {
        // Political gains resource management knowledge
        $political->crossPresetKnowledge += $resource->resourceFlow * 0.1 * $strength;
        
        // Resource gains political organization
        $resource->crossPresetKnowledge += $political->organizationLevel * 0.1 * $strength;
        
        // Both worlds develop hybrid characteristics
        if ($strength > 0.8) {
            $political->economicFocus += 0.2;
            $resource->governanceLevel += 0.2;
        }
    }
}
