<?php

namespace Tuzy\Application\World\Interaction;

use Tuzy\Domain\World\WorldState;
use Tuzy\Application\World\Interaction\Presets\PresetInteraction;
use App\Domains\World\Evolution\EvolutionEngine;

class HybridPresetGenerator
{
    private array $interactionRegistry = [];

    public function __construct()
    {
        $this->registerInteractions();
    }

    public function generateHybrid(
        EvolutionEngine $presetA, 
        EvolutionEngine $presetB, 
        float $interactionStrength
    ): EvolutionEngine {
        // Mix evolution equations
        $hybridEquations = $this->mixEvolutionEquations($presetA, $presetB, $interactionStrength);
        
        // Create hybrid collapse conditions
        $hybridCollapse = $this->combineCollapseConditions($presetA, $presetB);
        
        // Generate hybrid identity
        $hybridIdentity = $this->createHybridIdentity($presetA, $presetB, $interactionStrength);
        
        return new HybridEvolutionEngine($hybridEquations, $hybridCollapse, $hybridIdentity);
    }

    public function canCreateHybrid(WorldState $worldA, WorldState $worldB): bool
    {
        $interactionType = $this->getInteractionType($worldA, $worldB);
        
        if (!isset($this->interactionRegistry[$interactionType])) {
            return false;
        }

        $interaction = $this->interactionRegistry[$interactionType];
        return $interaction->canHybridize($worldA, $worldB);
    }

    public function getHybridCandidates(WorldState $world): array
    {
        $candidates = [];
        
        foreach ($this->interactionRegistry as $type => $interaction) {
            if ($this->isCompatibleWithWorld($world, $type)) {
                $candidates[] = [
                    'type' => $type,
                    'compatibility' => $interaction->calculateCompatibility($world, $world),
                    'requirements' => $this->getHybridRequirements($type)
                ];
            }
        }

        // Sort by compatibility
        usort($candidates, fn($a, $b) => $b['compatibility'] <=> $a['compatibility']);
        return array_slice($candidates, 0, 5);
    }

    private function mixEvolutionEquations(EvolutionEngine $presetA, EvolutionEngine $presetB, float $strength): array
    {
        return [
            'coherence_equation' => $this->mixEquation(
                $presetA->getCoherenceEquation(),
                $presetB->getCoherenceEquation(),
                $strength
            ),
            'entropy_equation' => $this->mixEquation(
                $presetA->getEntropyEquation(),
                $presetB->getEntropyEquation(),
                $strength
            ),
            'belief_equation' => $this->mixEquation(
                $presetA->getBeliefEquation(),
                $presetB->getBeliefEquation(),
                $strength
            ),
            'stability_equation' => $this->mixEquation(
                $presetA->getStabilityEquation(),
                $presetB->getStabilityEquation(),
                $strength
            )
        ];
    }

    private function mixEquation(string $eqA, string $eqB, float $strength): string
    {
        // Weighted mixing of equations
        $weightA = $strength;
        $weightB = 1 - $strength;
        
        return "({$weightA} * ($eqA)) + ({$weightB} * ($eqB))";
    }

    private function combineCollapseConditions(EvolutionEngine $presetA, EvolutionEngine $presetB): array
    {
        return [
            'primary_condition' => $this->combineConditions(
                $presetA->getPrimaryCollapseCondition(),
                $presetB->getPrimaryCollapseCondition()
            ),
            'secondary_conditions' => array_merge(
                $presetA->getSecondaryCollapseConditions(),
                $presetB->getSecondaryCollapseConditions()
            ),
            'threshold_modifier' => ($presetA->getCollapseThreshold() + $presetB->getCollapseThreshold()) / 2
        ];
    }

    private function createHybridIdentity(EvolutionEngine $presetA, EvolutionEngine $presetB, float $strength): array
    {
        $identityA = $presetA->getIdentity();
        $identityB = $presetB->getIdentity();
        
        return [
            'name' => $this->generateHybridName($identityA['name'], $identityB['name']),
            'description' => $this->generateHybridDescription($identityA, $identityB, $strength),
            'characteristics' => $this->blendCharacteristics(
                $identityA['characteristics'],
                $identityB['characteristics'],
                $strength
            ),
            'dominant_traits' => $this->selectDominantTraits(
                $identityA['dominant_traits'],
                $identityB['dominant_traits'],
                $strength
            )
        ];
    }

    private function generateHybridName(string $nameA, string $nameB): string
    {
        // Combine names meaningfully
        $prefixes = ['Scientific', 'Rational', 'Logical', 'Systematic'];
        $suffixes = ['Faith', 'Belief', 'Creed', 'Doctrine', 'Order'];
        
        if (strpos($nameA, 'Faith') !== false || strpos($nameB, 'Faith') !== false) {
            return $prefixes[array_rand($prefixes)] . ' ' . $suffixes[array_rand($suffixes)];
        }
        
        if (strpos($nameA, 'Political') !== false || strpos($nameB, 'Political') !== false) {
            return $prefixes[array_rand($prefixes)] . ' ' . 'Governance';
        }
        
        if (strpos($nameA, 'Resource') !== false || strpos($nameB, 'Resource') !== false) {
            return $prefixes[array_rand($prefixes)] . ' ' . 'Economics';
        }
        
        return 'Hybrid ' . $nameA . '-' . $nameB;
    }

    private function generateHybridDescription(array $identityA, array $identityB, float $strength): string
    {
        $descA = $identityA['description'];
        $descB = $identityB['description'];
        
        if ($strength > 0.8) {
            return "A powerful synthesis of {$descA} and {$descB}, creating unprecedented emergent properties.";
        } elseif ($strength > 0.5) {
            return "A balanced fusion of {$descA} with elements of {$descB}, offering unique evolutionary pathways.";
        } else {
            return "A tentative combination of {$descA} and {$descB}, with potential for greater integration.";
        }
    }

    private function blendCharacteristics(array $charsA, array $charsB, float $strength): array
    {
        $blended = [];
        
        // Weighted blending
        foreach ($charsA as $char) {
            $blended[$char] = ($blended[$char] ?? 0) + $strength;
        }
        
        foreach ($charsB as $char) {
            $blended[$char] = ($blended[$char] ?? 0) + (1 - $strength);
        }
        
        // Normalize and return top characteristics
        arsort($blended);
        return array_keys(array_slice($blended, 0, 5, true));
    }

    private function selectDominantTraits(array $traitsA, array $traitsB, float $strength): array
    {
        $allTraits = array_merge($traitsA, $traitsB);
        $weighted = [];
        
        foreach ($allTraits as $trait) {
            $weight = 0;
            if (in_array($trait, $traitsA)) $weight += $strength;
            if (in_array($trait, $traitsB)) $weight += (1 - $strength);
            $weighted[$trait] = $weight;
        }
        
        arsort($weighted);
        return array_keys(array_slice($weighted, 0, 3, true));
    }

    private function combineConditions(string $condA, string $condB): string
    {
        return "($condA) AND ($condB)";
    }

    private function registerInteractions(): void
    {
        $this->interactionRegistry = [
            'BELIEF_CONTAMINATION' => new \Tuzy\Application\World\Interaction\Presets\FaithRationalInteraction(),
            'RESOURCE_CROSSFLOW' => new \Tuzy\Application\World\Interaction\Presets\PoliticalResourceInteraction(),
            'REALITY_DISTORTION' => new \Tuzy\Application\World\Interaction\Presets\ChaoticStableInteraction()
        ];
    }

    private function getInteractionType(WorldState $worldA, WorldState $worldB): string
    {
        $presetA = $worldA->currentPreset;
        $presetB = $worldB->currentPreset;
        
        // Determine interaction type based on preset combination
        if (($presetA === 'faith' && $presetB === 'rational') || ($presetA === 'rational' && $presetB === 'faith')) {
            return 'BELIEF_CONTAMINATION';
        }
        
        if (($presetA === 'political' && $presetB === 'resource') || ($presetA === 'resource' && $presetB === 'political')) {
            return 'RESOURCE_CROSSFLOW';
        }
        
        if (($presetA === 'chaotic' && $presetB === 'stable') || ($presetA === 'stable' && $presetB === 'chaotic')) {
            return 'REALITY_DISTORTION';
        }
        
        return 'NARRATIVE_BLEED';
    }

    private function isCompatibleWithWorld(WorldState $world, string $interactionType): bool
    {
        $worldPreset = $world->currentPreset;
        
        $compatibility = [
            'BELIEF_CONTAMINATION' => ['faith', 'rational'],
            'RESOURCE_CROSSFLOW' => ['political', 'resource'],
            'REALITY_DISTORTION' => ['chaotic', 'stable']
        ];
        
        return in_array($worldPreset, $compatibility[$interactionType] ?? []);
    }

    private function getHybridRequirements(string $interactionType): array
    {
        return [
            'BELIEF_CONTAMINATION' => [
                'min_belief_mass' => 0.7,
                'min_data_consistency' => 0.8,
                'min_interaction_strength' => 0.7
            ],
            'RESOURCE_CROSSFLOW' => [
                'min_propaganda_effort' => 0.6,
                'min_scarcity_rate' => 0.8,
                'min_interaction_strength' => 0.6
            ],
            'REALITY_DISTORTION' => [
                'min_entropy' => 0.6,
                'min_coherence' => 0.4,
                'min_interaction_strength' => 0.5
            ]
        ];
    }
}
