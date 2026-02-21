<?php

declare(strict_types=1);

namespace Tuzy\Domain\Saga\Enums;

enum EpicEventType: string
{
    case STAGE_TRANSITION = 'stage_transition';
    case CATACLYSM = 'cataclysm';
    case MYTH_BIRTH = 'myth_birth';
    case GREAT_WAR = 'great_war';
    case DIVINE_INTERVENTION = 'divine_intervention';
    case WORLD_COLLAPSE = 'world_collapse';
    case ENTROPY_SPIKE = 'entropy_spike';
    case TERRAFORM_EVENT = 'terraform_event';

    public function label(): string
    {
        return match ($this) {
            self::STAGE_TRANSITION => 'Chuyển Giao Thời Đại',
            self::CATACLYSM => 'Đại Thảm Họa',
            self::MYTH_BIRTH => 'Thần Thoại Giáng Sinh',
            self::GREAT_WAR => 'Đại Chiến Thế Giới',
            self::DIVINE_INTERVENTION => 'Thần Tích',
            self::WORLD_COLLAPSE => 'Tận Thế',
            self::ENTROPY_SPIKE => 'Đột Biến Entropy',
            self::TERRAFORM_EVENT => 'Tái Thiết Thực Tại',
        };
    }
}
