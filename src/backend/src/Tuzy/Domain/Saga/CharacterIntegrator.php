<?php

namespace Tuzy\Domain\Saga;

use Illuminate\Support\Collection;

/**
 * CharacterIntegrator - Link WorldState events to character experiences
 * 
 * Maps event types to character roles and updates emotional states.
 */
class CharacterIntegrator
{
    /**
     * Integrate events with characters.
     * 
     * @param array $events Detected saga events
     * @param Collection $characters Characters in the world
     * @return array Integration results
     */
    public function integrate(array $events, Collection $characters): array
    {
        $results = [];

        foreach ($events as $event) {
            $affectedCharacters = $this->findAffectedCharacters($event, $characters);

            foreach ($affectedCharacters as $character) {
                $impact = $this->applyEventToCharacter($event, $character);
                
                $results[] = [
                    'event_type' => $event['type'],
                    'character_id' => $character->id,
                    'character_name' => $character->name,
                    'impact' => $impact,
                ];
            }
        }

        return $results;
    }

    /**
     * Find characters affected by event.
     */
    private function findAffectedCharacters(array $event, Collection $characters): Collection
    {
        return $characters->filter(function ($character) use ($event) {
            return $this->isCharacterAffected($event, $character);
        });
    }

    /**
     * Check if character is affected by event.
     */
    private function isCharacterAffected(array $event, $character): bool
    {
        // Map event types to character roles
        return match($event['type']) {
            'famine' => in_array($character->role ?? '', ['peasant', 'farmer', 'merchant']),
            'inequality_crisis' => in_array($character->role ?? '', ['noble', 'merchant', 'peasant']),
            'collapse_warning' => true, // All characters affected
            'trauma_accumulation' => true, // All characters affected
            'external_threat' => in_array($character->role ?? '', ['warrior', 'noble', 'guard']),
            'shock' => true, // All characters affected by sudden changes
            default => false,
        };
    }

    /**
     * Apply event to character state.
     */
    private function applyEventToCharacter(array $event, $character): array
    {
        $severity = $event['severity'] ?? 0.5;
        $impact = [];

        // Map event types to emotional impacts
        match($event['type']) {
            'famine' => $impact = $this->applyFamineImpact($character, $severity),
            'inequality_crisis' => $impact = $this->applyInequalityImpact($character, $severity),
            'collapse_warning' => $impact = $this->applyCollapseImpact($character, $severity),
            'trauma_accumulation' => $impact = $this->applyTraumaImpact($character, $severity),
            'external_threat' => $impact = $this->applyThreatImpact($character, $severity),
            'shock' => $impact = $this->applyShockImpact($character, $severity),
            default => null,
        };

        // Save character state (if using Eloquent model)
        if (method_exists($character, 'save')) {
            $character->save();
        }

        return $impact;
    }

    /**
     * Apply famine impact to character.
     */
    private function applyFamineImpact($character, float $severity): array
    {
        $this->addEmotion($character, 'desperation', $severity);
        $this->addEmotion($character, 'hunger', $severity);
        
        return [
            'desperation' => $severity,
            'hunger' => $severity,
        ];
    }

    /**
     * Apply inequality impact to character.
     */
    private function applyInequalityImpact($character, float $severity): array
    {
        $role = $character->role ?? 'peasant';
        
        if (in_array($role, ['noble', 'merchant'])) {
            $this->addEmotion($character, 'guilt', $severity * 0.3);
            return ['guilt' => $severity * 0.3];
        } else {
            $this->addEmotion($character, 'resentment', $severity);
            $this->addEmotion($character, 'anger', $severity * 0.7);
            return [
                'resentment' => $severity,
                'anger' => $severity * 0.7,
            ];
        }
    }

    /**
     * Apply collapse impact to character.
     */
    private function applyCollapseImpact($character, float $severity): array
    {
        $this->addEmotion($character, 'fear', $severity);
        $this->addEmotion($character, 'anxiety', $severity * 0.8);
        
        return [
            'fear' => $severity,
            'anxiety' => $severity * 0.8,
        ];
    }

    /**
     * Apply trauma impact to character.
     */
    private function applyTraumaImpact($character, float $severity): array
    {
        $this->addEmotion($character, 'grief', $severity);
        $this->addEmotion($character, 'numbness', $severity * 0.6);
        
        return [
            'grief' => $severity,
            'numbness' => $severity * 0.6,
        ];
    }

    /**
     * Apply threat impact to character.
     */
    private function applyThreatImpact($character, float $severity): array
    {
        $this->addEmotion($character, 'fear', $severity);
        $this->addEmotion($character, 'vigilance', $severity * 0.9);
        
        return [
            'fear' => $severity,
            'vigilance' => $severity * 0.9,
        ];
    }

    /**
     * Apply shock impact to character.
     */
    private function applyShockImpact($character, float $severity): array
    {
        $this->addEmotion($character, 'confusion', $severity);
        $this->addEmotion($character, 'disorientation', $severity * 0.7);
        
        return [
            'confusion' => $severity,
            'disorientation' => $severity * 0.7,
        ];
    }

    /**
     * Add emotion to character state.
     */
    private function addEmotion($character, string $emotion, float $intensity): void
    {
        // Assuming character has emotional_state JSON field
        $emotionalState = $character->emotional_state ?? [];
        
        // Add or update emotion
        $emotionalState[$emotion] = min(1.0, ($emotionalState[$emotion] ?? 0) + $intensity);
        
        $character->emotional_state = $emotionalState;
    }
}
