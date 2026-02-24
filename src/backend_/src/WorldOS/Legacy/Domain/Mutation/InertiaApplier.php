<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Mutation;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;

/**
 * Dampens delta by stability factor: effective_delta = delta * (1 - stability_factor).
 * High institutional strength/cohesion reduces impact of story mutation.
 */
class InertiaApplier
{
    public function apply(WorldStateVector $delta, float $stabilityFactor): WorldStateVector
    {
        $factor = max(0.0, min(1.0, $stabilityFactor));
        $damp = 1.0 - $factor;
        $components = $delta->getAll();
        $damped = array_map(fn ($v) => $v * $damp, $components);
        return WorldStateVector::fromArray($damped);
    }
}
