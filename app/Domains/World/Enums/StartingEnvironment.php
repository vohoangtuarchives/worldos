<?php

namespace App\Domains\World\Enums;

enum StartingEnvironment: string
{
    case CONTINENTAL = 'CONTINENTAL';     // Đại lục rộng lớn
    case ARCHIPELAGO = 'ARCHIPELAGO';     // Quần đảo
    case SKY_REALM = 'SKY_REALM';         // Thiên giới, đảo bay
    case UNDERGROUND = 'UNDERGROUND';     // Địa phủ, hang động
    case WASTELAND = 'WASTELAND';         // Hoang mạc, khô cằn

    public function label(): string
    {
        return match($this) {
            self::CONTINENTAL => '🌍 Đại Lục',
            self::ARCHIPELAGO => '🏝️ Quần Đảo',
            self::SKY_REALM => '☁️ Thiên Giới',
            self::UNDERGROUND => '🕳️ Địa Phủ',
            self::WASTELAND => '🏜️ Hoang Mạc',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::CONTINENTAL => 'Lục địa rộng lớn, nhiều vùng khí hậu, đường bộ nối liền',
            self::ARCHIPELAGO => 'Hàng ngàn hòn đảo, hàng hải là chìa khóa sinh tồn',
            self::SKY_REALM => 'Đảo bay trên mây, vực sâu vô đáy bên dưới',
            self::UNDERGROUND => 'Hang động vô tận, ánh sáng là tài nguyên quý nhất',
            self::WASTELAND => 'Khô cằn, oasis hiếm hoi, bão cát thường trực',
        };
    }
}
