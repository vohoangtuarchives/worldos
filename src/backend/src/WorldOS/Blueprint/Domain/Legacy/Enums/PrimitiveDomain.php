<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Enums;

enum PrimitiveDomain: string
{
    case CIVILIZATION = 'civilization';
    case CULTURE = 'culture';
    case ECONOMY = 'economy';
    case POWER = 'power';
    case ONTOLOGICAL = 'ontological';

    public function label(): string
    {
        return match ($this) {
            self::CIVILIZATION => '🏛️ Civilizational',
            self::CULTURE => '🎭 Cultural',
            self::ECONOMY => '💰 Economic',
            self::POWER => '⚡ Power',
            self::ONTOLOGICAL => '🌌 Ontological',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CIVILIZATION => 'How people organize society',
            self::CULTURE => 'How people think and believe',
            self::ECONOMY => 'How resources flow',
            self::POWER => 'Where authority comes from',
            self::ONTOLOGICAL => 'What can exist in this world',
        };
    }
}
