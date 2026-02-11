<?php

namespace App\Domains\World\Enums;

enum PowerCeiling: string
{
    case HUMAN = 'HUMAN';
    case HUMAN_PLUS = 'HUMAN_PLUS'; // Wuxia masters
    case TRANSCENDENT = 'TRANSCENDENT'; // Mages
    case IMMORTAL = 'IMMORTAL'; // Xianxia

    public function label(): string
    {
        return match($this) {
            self::HUMAN => '👤 Người Thường',
            self::HUMAN_PLUS => '🥋 Cao Thủ',
            self::TRANSCENDENT => '✨ Siêu Phàm',
            self::IMMORTAL => '☁️ Tiên Nhân',
        };
    }

}
