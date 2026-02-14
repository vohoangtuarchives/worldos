<?php

namespace App\Domains\Social\Enums;

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
