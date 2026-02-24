<?php

declare(strict_types=1);

namespace App\WorldOS\Resonance\ValueObjects;

/**
 * Resonance Event — describes a narrative trigger from physics thresholds.
 *
 * When WorldStateVector reaches critical thresholds, a ResonanceEvent fires.
 * This is the bridge from Left Brain (simulation) to Right Brain (narrative).
 */
final readonly class ResonanceEvent
{
    /**
     * @param string $type            Event type (e.g., 'hero_spawn', 'bifurcation')
     * @param HeroArchetype|null $archetype  Hero archetype if type is hero_spawn
     * @param float $magnitude        Strength of resonance trigger (0-1)
     * @param string $description     Human-readable description
     * @param array<string, float> $triggerConditions  State values that caused this
     */
    public function __construct(
        public string $type,
        public ?HeroArchetype $archetype,
        public float $magnitude,
        public string $description,
        public array $triggerConditions,
    ) {
    }

    /**
     * Create a hero spawn resonance event.
     *
     * @param array<string, float> $conditions
     */
    public static function heroSpawn(
        HeroArchetype $archetype,
        float $magnitude,
        string $description,
        array $conditions,
    ): self {
        return new self(
            type: 'hero_spawn',
            archetype: $archetype,
            magnitude: $magnitude,
            description: $description,
            triggerConditions: $conditions,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'archetype' => $this->archetype?->value,
            'magnitude' => $this->magnitude,
            'description' => $this->description,
            'trigger_conditions' => $this->triggerConditions,
        ];
    }
}
