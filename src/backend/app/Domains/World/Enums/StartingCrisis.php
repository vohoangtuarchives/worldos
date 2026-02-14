<?php

namespace App\Domains\World\Enums;

enum StartingCrisis: string
{
    case NONE = 'NONE';               // Thái bình — bão tố đang rình rập
    case WAR = 'WAR';                 // Chiến loạn giữa các thế lực
    case PLAGUE = 'PLAGUE';           // Đại dịch, bệnh lạ lan tràn
    case INVASION = 'INVASION';       // Ngoại xâm (quái vật / ngoại tộc)
    case FAMINE = 'FAMINE';           // Đại hạn, tài nguyên cạn kiệt
    case AWAKENING = 'AWAKENING';     // Sức mạnh bí ẩn thức tỉnh trong dân

    public function label(): string
    {
        return match($this) {
            self::NONE => '☀️ Thái Bình',
            self::WAR => '⚔️ Chiến Loạn',
            self::PLAGUE => '🦠 Đại Dịch',
            self::INVASION => '👾 Ngoại Xâm',
            self::FAMINE => '🥀 Đại Hạn',
            self::AWAKENING => '✨ Giác Tỉnh',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::NONE => 'Bắt đầu yên ổn — nhưng bão tố đang đến',
            self::WAR => 'Hai đại lực đối đầu, dân chúng lầm than',
            self::PLAGUE => 'Bệnh lạ lan tràn, thuốc là vàng, xã hội sụp đổ',
            self::INVASION => 'Thế lực bên ngoài xâm lược — quái vật hoặc ngoại tộc',
            self::FAMINE => 'Tài nguyên cạn kiệt, di cư hàng loạt, sinh tồn khốc liệt',
            self::AWAKENING => 'Sức mạnh bí ẩn bất ngờ thức giấc trong dân thường',
        };
    }
}
