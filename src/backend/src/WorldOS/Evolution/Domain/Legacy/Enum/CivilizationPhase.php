<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Enum;

/**
 * CivilizationPhase
 * 
 * Represents the internal historical dynamics of a civilization.
 */
enum CivilizationPhase: string
{
    case PRIMITIVE = 'primitive';         // Tribal/Early social structures
    case STABILITY = 'stability';         // Balanced growth, predictable dynamics
    case GOLDEN_AGE = 'golden_age';       // Peak prosperity and cultural energy
    case STAGNATION = 'stagnation';       // High prosperity but decaying institutions
    case FRAGMENTATION = 'fragmentation'; // Social collapse, internal entropy rise
    case WAR = 'war';                     // High military pressure, resource drain
    case REFORM = 'reform';               // Dynamic restructuring, legitimacy test
    case EMERGENCE = 'emergence';         // Transitioning from primitive to stable
    case EXTINCT = 'extinct';             // Remaining traces of a dead civilization
    case ILLUMINATION = 'illumination';   // Golden Transcendence State: Mythic era

    public function label(): string
    {
        return match($this) {
            self::PRIMITIVE => 'Nguyên thủy',
            self::STABILITY => 'Ổn định',
            self::GOLDEN_AGE => 'Hoàng kim',
            self::STAGNATION => 'Trì trệ',
            self::FRAGMENTATION => 'Tan rã',
            self::WAR => 'Chiến tranh',
            self::REFORM => 'Cải cách',
            self::EMERGENCE => 'Trỗi dậy',
            self::EXTINCT => 'Tuyệt chủng',
            self::ILLUMINATION => 'Khai sáng',
        };
    }
}
