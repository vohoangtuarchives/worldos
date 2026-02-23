<?php

namespace WorldOS\Legacy\Domain\Epistemology\Events;

use WorldOS\Legacy\Application\Epistemology\Entities\HistoricalFact;

/**
 * Triggered when epistemic instability leads to the mutation of a historical fact in the PerceivedArchive.
 */
class EpistemicRippleOccurred
{
    public function __construct(
        public readonly string $sagaId,
        public readonly string $universeId,
        public readonly HistoricalFact $originalFact,
        public readonly HistoricalFact $mutatedFact,
        public readonly float $instabilityTriggerLevel
    ) {}
}
