<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Arc\ValueObject;

/**
 * ArcType — Defines the narrative nature of a specific Arc (Segment of timeline).
 * Determined purely by mathematical analysis of the macro StateVector trajectory.
 */
enum ArcType: string
{
    /** Setup period, tension is low, entropy is low. Information gathering. */
    case INCITING = 'inciting';

    /** Tension is rising steadily, stability is declining. Rising action. */
    case ESCALATION = 'escalation';

    /** Sudden spike in entropy or anomaly index. A major destabilizing event. */
    case CRISIS = 'crisis';

    /** Tension hits a local maximum. The breaking point. */
    case CLIMAX = 'climax';

    /** Tension dropping, stability recovering. Falling action / Denouement. */
    case RESOLUTION = 'resolution';
}
