<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Enum;

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
        return match($this) {
            self::STAGE_0_MUNDANE => 0,
            self::STAGE_1_MORTAL_MARTIAL => 1,
            self::STAGE_2_ENHANCED_MARTIAL => 2,
            self::STAGE_3_LOW_IMMORTAL => 3,
            self::STAGE_4_HIGH_IMMORTAL => 4,
            self::STAGE_5_MYTHIC => 5,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::STAGE_0_MUNDANE => 'Người Thường (Mundane)',
            self::STAGE_1_MORTAL_MARTIAL => 'Võ Học Sơ Khai (Mortal Martial)',
            self::STAGE_2_ENHANCED_MARTIAL => 'Dị Năng / Chân Khí (Enhanced)',
            self::STAGE_3_LOW_IMMORTAL => 'Tu Tiên / Ma Pháp (Low Immortal)',
            self::STAGE_4_HIGH_IMMORTAL => 'Pháp Tắc Thực Tại (High Immortal)',
            self::STAGE_5_MYTHIC => 'Thần Thoại (Mythic)',
        };
    }
}
