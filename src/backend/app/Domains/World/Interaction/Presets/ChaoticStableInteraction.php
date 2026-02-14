<?php

namespace App\Domains\World\Interaction\Presets;

use App\Domains\World\WorldState;

class ChaoticStableInteraction implements PresetInteraction
{
    public function applyInteraction(WorldState $chaotic, WorldState $stable): void
    {
        $interactionStrength = $this->calculateInteractionStrength($chaotic, $stable);
        
        // Chaos infects stability
        $stable->entropy += $chaotic->entropy * 0.4 * $interactionStrength;
        $stable->coherence -= $chaotic->contradictionIndex * 0.2 * $interactionStrength;
        
        // But chaos also brings innovation
        $stable->innovationPressure += $chaotic->randomness * 0.3 * $interactionStrength;
        $stable->creativeDestruction += $chaotic->entropy * 0.25 * $interactionStrength;
        
        // Chaotic world might stabilize from contact
        if ($stable->coherence > 0.8) {
            $chaotic->entropy *= (1 - 0.2 * $interactionStrength);
            $chaotic->contradictionIndex *= (1 - 0.1 * $interactionStrength);
            $chaotic->stabilizationInfluence += $interactionStrength * 0.1;
        }
        
        // Stable world might become more flexible
        if ($chaotic->randomness > 0.7) {
            $stable->rigidity -= 0.15 * $interactionStrength;
            $stable->adaptability += 0.2 * $interactionStrength;
            $stable->entropy += 0.1 * $interactionStrength; // Controlled chaos
        }
        
        $this->applyMutualEffects($chaotic, $stable, $interactionStrength);
    }

    public function getInteractionType(): string
    {
        return 'REALITY_DISTORTION';
    }

    public function calculateCompatibility(WorldState $worldA, WorldState $worldB): float
    {
        $chaotic = ($worldA->currentPreset === 'chaotic') ? $worldA : $worldB;
        $stable = ($worldA->currentPreset === 'stable') ? $worldA : $worldB;
        
        // Compatibility based on balance potential
        $chaoticBalance = 1 - abs($chaotic->entropy - 0.6); // Not too chaotic
        $stableBalance = 1 - abs($stable->coherence - 0.7); // Not too rigid
        
        $complementarity = abs($chaotic->entropy - (1 - $stable->coherence));
        
        return ($chaoticBalance + $stableBalance + $complementarity) / 3;
    }

    public function canHybridize(WorldState $worldA, WorldState $worldB): bool
    {
        $strength = $this->calculateInteractionStrength($worldA, $worldB);
        $compatibility = $this->calculateCompatibility($worldA, $worldB);
        
        // Hybridization possible when chaos and stability are balanced
        return $strength > 0.5 && $compatibility > 0.6;
    }

    private function calculateInteractionStrength(WorldState $chaotic, WorldState $stable): float
    {
        // Strong interaction when chaos meets order
        $entropyDiff = abs($chaotic->entropy - $stable->entropy);
        $coherenceDiff = abs($chaotic->coherence - $stable->coherence);
        
        return (
            ($chaotic->dominanceLevel * $stable->permeability) +
            ($stable->dominanceLevel * $chaotic->permeability)
        ) * (1 + $entropyDiff * 0.3) * (1 + $coherenceDiff * 0.2);
    }

    private function applyMutualEffects(WorldState $chaotic, WorldState $stable, float $strength): void
    {
        // Both worlds gain cross-preset understanding
        $chaotic->crossPresetKnowledge += $stable->coherence * 0.1 * $strength;
        $stable->crossPresetKnowledge += $chaotic->entropy * 0.1 * $strength;
        
        // Emergent properties at high interaction
        if ($strength > 0.8) {
            $this->spawnEmergentProperties($chaotic, $stable, $strength);
        }
    }

    private function spawnEmergentProperties(WorldState $chaotic, WorldState $stable, float $strength): void
    {
        // Controlled chaos in stable world
        $stable->emergentProperties[] = [
            'type' => 'CONTROLLED_CHAOS',
            'source' => $chaotic->id,
            'strength' => $strength * 0.5,
            'description' => 'Stable world adopts controlled randomness'
        ];
        
        // Structured patterns in chaotic world
        $chaotic->emergentProperties[] = [
            'type' => 'EMERGENT_ORDER',
            'source' => $stable->id,
            'strength' => $strength * 0.5,
            'description' => 'Chaotic world develops stable patterns'
        ];
        
        // Potential for hybrid "Orderly Chaos" preset
        if ($strength > 0.9) {
            $chaotic->pendingHybrid = [
                'type' => 'orderly_chaos',
                'partner_id' => $stable->id,
                'strength' => $strength
            ];
            
            $stable->pendingHybrid = [
                'type' => 'orderly_chaos',
                'partner_id' => $chaotic->id,
                'strength' => $strength
            ];
        }
    }
}
