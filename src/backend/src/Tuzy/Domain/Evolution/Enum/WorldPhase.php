<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Enum;

/**
 * WorldPhase
 * 
 * Represents the global evolutionary basin of the world.
 * Unlike CivilizationPhase, this persists even if no civilization exists.
 */
enum WorldPhase: string
{
    case PRIMORDIAL = 'primordial';         // Initial state, life starting
    case BIOLOGICAL_AGE = 'biological_age'; // High biological diversity, no intelligence yet
    case CIVILIZATIONAL_AGE = 'civilizational_age'; // Dominated by intelligent societies
    case POST_CATASTROPHE = 'post_catastrophe'; // Cooling down/recovery after extinction
    case ARCANE_SHIFT = 'arcane_shift';     // Reality warped, allowing magical emergence
    case ENERGY_DOMINANT = 'energy_dominant'; // Pure energy life/formless state
    case TRANSCENDENT = 'transcendent';     // World reaching higher dimension/end of cycle

    public function label(): string
    {
        return match($this) {
            self::PRIMORDIAL => 'Sơ khai (Primordial)',
            self::BIOLOGICAL_AGE => 'Kỷ nguyên Sinh học',
            self::CIVILIZATIONAL_AGE => 'Kỷ nguyên Văn minh',
            self::POST_CATASTROPHE => 'Hậu tận thế (Recovery)',
            self::ARCANE_SHIFT => 'Thế giới Huyền bí (Arcane Awakening)',
            self::ENERGY_DOMINANT => 'Kỷ nguyên Năng lượng',
            self::TRANSCENDENT => 'Siêu việt (Transcendent)',
        };
    }
}
