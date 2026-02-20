<?php

namespace App\Domains\Epistemology\Events;

use App\Domains\Epistemology\Entities\HistoricalFact;

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
