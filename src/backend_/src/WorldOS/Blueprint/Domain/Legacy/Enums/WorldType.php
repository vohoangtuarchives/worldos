<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Enums;

enum WorldType: string
{
    case XIANXIA = 'XIANXIA';
    case FANTASY = 'FANTASY';
    case SCIFI = 'SCIFI';
    case HORROR = 'HORROR';
    case MYSTERY = 'MYSTERY';
    case CULTIVATION = 'CULTIVATION';
    case WUXIA = 'WUXIA';
    case MODERN_FANTASY = 'MODERN_FANTASY';
    case POST_APOCALYPTIC = 'POST_APOCALYPTIC';
    case AUTO_GENERATED = 'AUTO_GENERATED';

    public function label(): string
    {
        return match ($this) {
            self::XIANXIA => 'Xianxia',
            self::FANTASY => 'Fantasy',
            self::SCIFI => 'Sci-Fi',
            self::HORROR => 'Horror',
            self::MYSTERY => 'Mystery',
            self::CULTIVATION => 'Cultivation',
            self::WUXIA => 'Wuxia',
            self::MODERN_FANTASY => 'Modern Fantasy',
            self::POST_APOCALYPTIC => 'Post-Apocalyptic',
            self::AUTO_GENERATED => 'Auto Generated',
        };
    }
}
