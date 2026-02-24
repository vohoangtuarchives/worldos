<?php

declare(strict_types=1);

namespace App\WorldOS\Resonance\Events;

use App\WorldOS\Resonance\ValueObjects\ResonanceEvent;
use App\WorldOS\Runtime\ValueObjects\UniverseId;

/**
 * Domain Event: Resonance has been detected in a Universe.
 *
 * This event signals the Narrative side that physics thresholds
 * have been reached and narrative agents should be spawned.
 */
final readonly class ResonanceDetected
{
    /**
     * @param ResonanceEvent[] $resonanceEvents
     */
    public function __construct(
        public UniverseId $universeId,
        public int $tick,
        public array $resonanceEvents,
    ) {
    }
}
