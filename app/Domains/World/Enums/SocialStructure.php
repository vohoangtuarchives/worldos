<?php

namespace App\Domains\World\Enums;

enum SocialStructure: string
{
    case EMPIRE = 'EMPIRE';             // Đế chế trung ương tập quyền
    case SECTS = 'SECTS';               // Tông phái cạnh tranh
    case TRIBES = 'TRIBES';             // Bộ lạc phân tán
    case CITY_STATES = 'CITY_STATES';   // Thành bang thương nghiệp
    case ANARCHY = 'ANARCHY';           // Hỗn loạn, không trật tự

    public function label(): string
    {
        return match($this) {
            self::EMPIRE => '👑 Đế Chế',
            self::SECTS => '⛩️ Tông Phái',
            self::TRIBES => '🏕️ Bộ Lạc',
            self::CITY_STATES => '🏛️ Thành Bang',
            self::ANARCHY => '💀 Hỗn Loạn',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::EMPIRE => 'Trung ương tập quyền, quan liêu, triều đình, luật pháp chặt chẽ',
            self::SECTS => 'Các môn phái cạnh tranh, sư đồ truyền thừa, liên minh phân chia',
            self::TRIBES => 'Phân tán, tín ngưỡng đa dạng, chiến tranh bộ lạc, du mục',
            self::CITY_STATES => 'Thương nghiệp phát triển, ngoại giao, liên minh tạm thời',
            self::ANARCHY => 'Không trật tự, kẻ mạnh sinh tồn, luật rừng',
        };
    }
}
