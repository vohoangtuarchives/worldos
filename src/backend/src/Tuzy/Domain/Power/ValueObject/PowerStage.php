<?php

declare(strict_types=1);

namespace Tuzy\Domain\Power\ValueObject;

/**
 * Power progression stage (mundane → mythic).
 * Domain-only enum.
 */
enum PowerStage: string
{
    case STAGE_0_MUNDANE = 'mundane';
    case STAGE_1_MORTAL_MARTIAL = 'mortal_martial';
    case STAGE_2_ENHANCED_MARTIAL = 'enhanced_martial';
    case STAGE_3_LOW_IMMORTAL = 'low_immortal';
    case STAGE_4_HIGH_IMMORTAL = 'high_immortal';
    case STAGE_5_MYTHIC = 'mythic';

    public function level(): int
    {
        return match ($this) {
            self::STAGE_0_MUNDANE => 0,
            self::STAGE_1_MORTAL_MARTIAL => 1,
            self::STAGE_2_ENHANCED_MARTIAL => 2,
            self::STAGE_3_LOW_IMMORTAL => 3,
            self::STAGE_4_HIGH_IMMORTAL => 4,
            self::STAGE_5_MYTHIC => 5,
        };
    }
}
