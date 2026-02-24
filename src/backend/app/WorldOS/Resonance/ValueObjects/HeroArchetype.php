<?php

declare(strict_types=1);

namespace App\WorldOS\Resonance\ValueObjects;

/**
 * Hero Archetype — resonance-spawned character types.
 *
 * Each archetype is triggered by specific physics thresholds.
 * From docs: Entropy>0.8→REBEL, >0.9→SAVIOR, Order>0.9→REFORMER, etc.
 */
enum HeroArchetype: string
{
    case REBEL_LEADER = 'rebel_leader';
    case SAVIOR = 'savior';
    case REFORMER = 'reformer';
    case PHILOSOPHER_KING = 'philosopher_king';
    case CULTURAL_HERO = 'cultural_hero';

    /**
     * Get a human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::REBEL_LEADER => 'Rebel Leader',
            self::SAVIOR => 'Savior',
            self::REFORMER => 'Reformer',
            self::PHILOSOPHER_KING => 'Philosopher King',
            self::CULTURAL_HERO => 'Cultural Hero',
        };
    }

    /**
     * Get a short description of this archetype's narrative role.
     */
    public function description(): string
    {
        return match ($this) {
            self::REBEL_LEADER => 'Rises against entropy and inequality',
            self::SAVIOR => 'Emerges from existential threat to transform civilization',
            self::REFORMER => 'Seeks change within rigid authoritarian structures',
            self::PHILOSOPHER_KING => 'Enlightened absolutist who redesigns the system',
            self::CULTURAL_HERO => 'Unifies fragmented tribes into coherent culture',
        };
    }
}
