<?php

declare(strict_types=1);

namespace WorldOS\Society\Social\Enums;

enum RelationshipTone: string
{
    case NEUTRAL = 'neutral';
    case RESPECT = 'respect';
    case INTIMATE = 'intimate';
    case HOSTILE = 'hostile';
}
