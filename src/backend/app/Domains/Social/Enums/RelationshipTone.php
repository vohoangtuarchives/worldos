<?php

namespace App\Domains\Social\Enums;

enum RelationshipTone: string
{
    case NEUTRAL = 'neutral';
    case RESPECT = 'respect';
    case INTIMATE = 'intimate';
    case HOSTILE = 'hostile';
}
