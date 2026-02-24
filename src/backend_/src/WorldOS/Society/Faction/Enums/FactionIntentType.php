<?php

declare(strict_types=1);

namespace WorldOS\Society\Faction\Enums;

enum FactionIntentType: string
{
    case SURVIVE = 'survive';
    case EXPAND = 'expand';
    case ATTACK = 'attack';
    case ALLIANCE = 'alliance';
    case INVOKE_MYTH = 'invoke_myth';
    case SUPPRESS_SCAR = 'suppress_scar';
    case SPLIT = 'split';
    case RECOVER = 'recover';
    case STABILIZE = 'stabilize';
    case PURGE = 'purge';
    case REFORM = 'reform';
    case ADAPT = 'adapt';
}
