<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Saga\ValueObject;

/**
 * LifecyclePhase — The overarching stages of a 300+ chapter Saga.
 * It tracks the macro pacing of the entire narrative.
 */
enum LifecyclePhase: string
{
    case SEED         = 'seed';         // Before spawn
    case EMERGENCE    = 'emergence';    // Ch 0-40: Setup and entry into the world
    case ASCENT       = 'ascent';       // Ch 40-150: Core progress and conflict building
    case PEAK         = 'peak';         // Ch 150-220: The highest sustained tension and power
    case DESTABILIZE  = 'destabilize';  // Ch 220-260: Structure starts breaking down (NPI drop)
    case RESOLUTION   = 'resolution';   // Ch 260+: Ending attractor evaluation
    case ARCHIVED     = 'archived';     // Completed and structural genome saved
}
