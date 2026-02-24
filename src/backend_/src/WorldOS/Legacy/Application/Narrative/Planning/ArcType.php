<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\Planning;

/**
 * Story arc archetype for structure engine.
 */
enum ArcType: string
{
    case REBELLION = 'rebellion';
    case RISE_AND_FALL = 'rise_and_fall';
    case POWER_CONSOLIDATION = 'power_consolidation';
}
