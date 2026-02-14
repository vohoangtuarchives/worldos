<?php

namespace App\Domains\World\Enums;

enum TechLevel: string
{
    case PRIMITIVE = 'PRIMITIVE';
    case MEDIEVAL = 'MEDIEVAL';
    case DYNASTIC = 'DYNASTIC';
    case EARLY_INDUSTRIAL = 'EARLY_INDUSTRIAL';
    case MODERN = 'MODERN';
    case FUTURISTIC = 'FUTURISTIC';

    public function label(): string
    {
        return match($this) {
            self::PRIMITIVE => '🦴 Sơ Khai',
            self::MEDIEVAL => '🏰 Trung Cổ',
            self::DYNASTIC => '📜 Phong Kiến',
            self::EARLY_INDUSTRIAL => '🏭 Công Nghiệp Sớm',
            self::MODERN => '🏙️ Hiện Đại',
            self::FUTURISTIC => '🚀 Tương Lai',
        };
    }

}
