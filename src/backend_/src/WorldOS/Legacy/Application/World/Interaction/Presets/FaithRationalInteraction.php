<?php

namespace WorldOS\Legacy\Application\World\Interaction\Presets;

use WorldOS\Blueprint\Domain\Legacy\WorldState;

class FaithRationalInteraction implements PresetInteraction
{
    public function applyInteraction(WorldState $faith, WorldState $rational): void
    {
        $interactionStrength = $this->calculateInteractionStrength($faith, $rational);
        
        // Faith beliefs challenge rational data
        if ($faith->beliefMass > 0.7 && $rational->dataConsistency > 0.8) {
            $faith->contradictionIndex += 0.2 * $interactionStrength;
            $rational->anomalyRate += 0.15 * $interactionStrength;
            
            // Potential hybrid: "Scientific Religion" preset
            if ($interactionStrength > 0.8) {
                $this->spawnHybridPreset($faith, $rational, 'scientific_religion');
            }
        }
        
        // Rational analysis affects faith
        if ($rational->explanatoryCapacity > 0.6) {
            $faith->ritualDensity -= 0.1 * $interactionStrength;
            $faith->coherence += 0.05 * $interactionStrength;
        }
        
        // Faith affects rational perception
        if ($faith->beliefMass > 0.8) {
            $rational->dataConsistency -= 0.08 * $interactionStrength;
            $rational->anomalyRate += 0.1 * $interactionStrength;
        }
        
        // Cross-preset resource effects
        $this->applyResourceExchange($faith, $rational, $interactionStrength);
    }

    public function getInteractionType(): string
    {
        return 'BELIEF_CONTAMINATION';
    }

    public function calculateCompatibility(WorldState $worldA, WorldState $worldB): float
    {
        $faith = ($worldA->currentPreset === 'faith') ? $worldA : $worldB;
        $rational = ($worldA->currentPreset === 'rational') ? $worldA : $worldB;
        
        // Higher compatibility when both are strong but not extreme
        $faithScore = min($faith->beliefMass, 0.9) - max($faith->contradictionIndex - 0.5, 0);
        $rationalScore = min($rational->dataConsistency, 0.9) - max($rational->anomalyRate - 0.3, 0);
        
        return ($faithScore + $rationalScore) / 2;
    }

    public function canHybridize(WorldState $worldA, WorldState $worldB): bool
    {
        $strength = $this->calculateInteractionStrength($worldA, $worldB);
        $compatibility = $this->calculateCompatibility($worldA, $worldB);
        
        return $strength > 0.7 && $compatibility > 0.6;
    }

    private function calculateInteractionStrength(WorldState $faith, WorldState $rational): float
    {
        return min(
            ($faith->beliefMass * $rational->dataConsistency),
            ($faith->dominanceLevel * $rational->permeability)
        );
    }

    private function spawnHybridPreset(WorldState $faith, WorldState $rational, string $hybridType): void
    {
        // Mark both worlds for hybrid transformation
        $faith->pendingHybrid = [
            'type' => $hybridType,
            'partner_id' => $rational->id,
            'strength' => $this->calculateInteractionStrength($faith, $rational)
        ];
        
        $rational->pendingHybrid = [
            'type' => $hybridType,
            'partner_id' => $faith->id,
            'strength' => $this->calculateInteractionStrength($faith, $rational)
        ];
    }

    private function applyResourceExchange(WorldState $faith, WorldState $rational, float $strength): void
    {
        // Faith provides "belief resources" to rational
        $rational->narrativeResources += $faith->beliefMass * 0.1 * $strength;
        
        // Rational provides "data resources" to faith
        $faith->narrativeResources += $rational->dataConsistency * 0.1 * $strength;
        
        // Both worlds gain cross-preset understanding
        $faith->crossPresetKnowledge += $strength * 0.05;
        $rational->crossPresetKnowledge += $strength * 0.05;
    }
}
