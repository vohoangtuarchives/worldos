<?php

namespace App\StoryEngine\Character;

use App\Domains\Material\Material;
use App\Domains\Material\MaterialInstance;

class CharacterTraitDeriver
{
    private MaterialCharacterAffinity $affinity;

    public function __construct(MaterialCharacterAffinity $affinity)
    {
        $this->affinity = $affinity;
    }

    /**
     * Derive character traits from material and instance state.
     */
    public function deriveTraits(Material $material, MaterialInstance $instance): array
    {
        $baseTraits = $this->affinity->getRandomTraits($material->code, 3);
        
        // Add contextual traits based on material state
        $contextualTraits = $this->deriveContextualTraits($material, $instance);
        
        return array_unique(array_merge($baseTraits, $contextualTraits));
    }

    /**
     * Derive contextual traits based on material instance state.
     */
    private function deriveContextualTraits(Material $material, MaterialInstance $instance): array
    {
        $traits = [];

        // Strength-based traits
        if ($instance->strength_level >= 8) {
            $traits[] = 'dominant';
            $traits[] = 'influential';
        } elseif ($instance->strength_level <= 3) {
            $traits[] = 'struggling';
            $traits[] = 'emerging';
        }

        // Mutation-based traits
        $mutationState = $instance->mutation_state ?? [];
        if (isset($mutationState['mutated_from'])) {
            $traits[] = 'transformed';
            $traits[] = 'evolved';
        }

        // Ontology-based traits
        switch ($material->ontology->value) {
            case 'symbolic':
                $traits[] = 'symbolic';
                break;
            case 'institutional':
                $traits[] = 'structured';
                break;
            case 'behavioral':
                $traits[] = 'active';
                break;
        }

        // Function-based traits
        switch ($material->function->value) {
            case 'legitimizing':
                $traits[] = 'authoritative';
                break;
            case 'stabilizing':
                $traits[] = 'conservative';
                break;
            case 'transformative':
                $traits[] = 'revolutionary';
                break;
            case 'destructive':
                $traits[] = 'chaotic';
                break;
        }

        return $traits;
    }

    /**
     * Derive personality archetype from traits.
     */
    public function derivePersonality(array $traits): string
    {
        // Simple personality mapping based on trait combinations
        if (in_array('authoritative', $traits) && in_array('sacred', $traits)) {
            return 'Divine Authority';
        }
        
        if (in_array('cunning', $traits) && in_array('subversive', $traits)) {
            return 'Clever Rebel';
        }
        
        if (in_array('brave', $traits) && in_array('transformative', $traits)) {
            return 'Heroic Transformer';
        }
        
        if (in_array('vengeful', $traits) && in_array('honor-bound', $traits)) {
            return 'Honorable Avenger';
        }

        return 'Complex Individual';
    }
}
