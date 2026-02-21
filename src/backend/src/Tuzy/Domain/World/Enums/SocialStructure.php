<?php

declare(strict_types=1);

namespace Tuzy\Domain\World\Enums;

enum SocialStructure: string
{
    case EMPIRE = 'EMPIRE';
    case SECTS = 'SECTS';
    case TRIBES = 'TRIBES';
    case CITY_STATES = 'CITY_STATES';
    case ANARCHY = 'ANARCHY';

    public function label(): string
    {
        return match ($this) {
            self::EMPIRE => 'Empire',
            self::SECTS => 'Sects',
            self::TRIBES => 'Tribes',
            self::CITY_STATES => 'City States',
            self::ANARCHY => 'Anarchy',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::EMPIRE => 'Centralized',
            self::SECTS => 'Sects',
            self::TRIBES => 'Tribes',
            self::CITY_STATES => 'City states',
            self::ANARCHY => 'Anarchy',
        };
    }
}
