<?php

declare(strict_types=1);

namespace App\WorldOS\Resonance\Contracts;

use App\WorldOS\Resonance\ValueObjects\ResonanceEvent;
use App\WorldOS\Shared\ValueObjects\CascadeStateVector;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * Resonance Checker Contract.
 *
 * Analyzes WorldStateVector for resonance triggers (narrative events).
 * Implementations define threshold rules for hero spawning, bifurcation, etc.
 */
interface ResonanceCheckerInterface
{
    /**
     * Check if current state triggers any resonance events.
     *
     * @return ResonanceEvent[] List of triggered events (may be empty)
     */
    public function check(
        WorldStateVector $state,
        CascadeStateVector $cascade,
    ): array;
}
