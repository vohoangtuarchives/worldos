<?php

declare(strict_types=1);

namespace Tuzy\Domain\Social\Enums;

enum SocialImpactEvent: string
{
    case SAVED_LIFE = 'saved_life';
    case BETRAYAL = 'betrayal';
    case DEFEAT = 'defeat';
    case TEACHING = 'teaching';
    case INSULT = 'insult';
    case COOPERATION = 'cooperation';
    case SHARED_TRAUMA = 'shared_trauma';
}
