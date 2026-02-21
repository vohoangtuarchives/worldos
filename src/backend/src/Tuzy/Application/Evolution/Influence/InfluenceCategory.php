<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\Influence;

/**
 * WorldOS 2.0 Clean: Influence theo category, không theo feature.
 * Thứ tự áp dụng: Structural → Cultural → ExternalPressure → NarrativePressure → PlayerDecision → Meta.
 */
enum InfluenceCategory: string
{
    case Structural = 'structural';
    case Cultural = 'cultural';
    case ExternalPressure = 'external_pressure';
    case NarrativePressure = 'narrative_pressure';
    case PlayerDecision = 'player_decision';
    case Meta = 'meta';

    public function order(): int
    {
        return match ($this) {
            self::Structural => 0,
            self::Cultural => 1,
            self::ExternalPressure => 2,
            self::NarrativePressure => 3,
            self::PlayerDecision => 4,
            self::Meta => 5,
        };
    }
}
