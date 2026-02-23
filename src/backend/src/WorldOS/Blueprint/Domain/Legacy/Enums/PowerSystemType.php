<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Enums;

enum PowerSystemType: string
{
    case INTERNAL_QI = 'INTERNAL_QI';
    case EVOLUTION = 'EVOLUTION';
    case ANOMALY = 'ANOMALY';
    case SPIRITUAL_QI = 'SPIRITUAL_QI';
    case MANA = 'MANA';
    case DARK_MANA = 'DARK_MANA';
    case DEMONIC_QI = 'DEMONIC_QI';
    case NEN = 'NEN';
    case CHAKRA = 'CHAKRA';
    case SPIRITUAL_SENSE = 'SPIRITUAL_SENSE';
    case OCCULT_RITUAL = 'OCCULT_RITUAL';
    case TALISMAN = 'TALISMAN';
    case COSMIC_RULE = 'COSMIC_RULE';
    case MULTIVERSE = 'MULTIVERSE';
    case SYSTEM_STATS = 'SYSTEM_STATS';
    case TECH_IMPLANT = 'TECH_IMPLANT';
    case VR_SIMULATION = 'VR_SIMULATION';
    case NONE = 'NONE';
    case MIXED = 'MIXED';

    public function pillar(): string
    {
        return match ($this) {
            self::INTERNAL_QI, self::EVOLUTION, self::ANOMALY => 'tinh',
            self::SPIRITUAL_QI, self::MANA, self::DARK_MANA, self::DEMONIC_QI, self::NEN, self::CHAKRA => 'khi',
            self::SPIRITUAL_SENSE, self::OCCULT_RITUAL, self::TALISMAN => 'than',
            self::COSMIC_RULE, self::MULTIVERSE, self::SYSTEM_STATS => 'quy_tac',
            self::TECH_IMPLANT, self::VR_SIMULATION => 'cong_nghe',
            self::NONE, self::MIXED => 'none',
        };
    }

    public function label(): string
    {
        return $this->value;
    }
}
