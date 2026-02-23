<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Enums;

enum StartingEnvironment: string
{
    case CONTINENTAL = 'CONTINENTAL';
    case ARCHIPELAGO = 'ARCHIPELAGO';
    case SKY_REALM = 'SKY_REALM';
    case UNDERGROUND = 'UNDERGROUND';
    case WASTELAND = 'WASTELAND';

    public function label(): string
    {
        return match ($this) {
            self::CONTINENTAL => 'Continental',
            self::ARCHIPELAGO => 'Archipelago',
            self::SKY_REALM => 'Sky Realm',
            self::UNDERGROUND => 'Underground',
            self::WASTELAND => 'Wasteland',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CONTINENTAL => 'Large continent',
            self::ARCHIPELAGO => 'Island chains',
            self::SKY_REALM => 'Floating realm',
            self::UNDERGROUND => 'Underground',
            self::WASTELAND => 'Wasteland',
        };
    }
}
