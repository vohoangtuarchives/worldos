<?php

namespace App\Domains\Faction\Enums;

enum FactionIntentType: string
{
    case EXPAND = 'expand';
    case ATTACK = 'attack';
    case ALLIANCE = 'alliance';
    case INVOKE_MYTH = 'invoke_myth';
    case SUPPRESS_SCAR = 'suppress_scar';
    case SPLIT = 'split';
    case RECOVER = 'recover';
    case STABILIZE = 'stabilize';
}
