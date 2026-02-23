<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Mutation;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;

/**
 * Clamps delta per dimension and optional total energy budget.
 */
class MutationLimiter
{
    public function __construct(
        private readonly float $maxDeltaPerDimension = 0.1,
        private readonly ?float $maxTotalMagnitude = null,
    ) {
    }

    public function limit(WorldStateVector $delta): WorldStateVector
    {
        $components = $delta->getAll();
        $clamped = [];
        foreach ($components as $dim => $val) {
            $clamped[$dim] = max(-$this->maxDeltaPerDimension, min($this->maxDeltaPerDimension, $val));
        }
        $result = WorldStateVector::fromArray($clamped);

        if ($this->maxTotalMagnitude !== null && $result->magnitude() > $this->maxTotalMagnitude) {
            $scale = $this->maxTotalMagnitude / $result->magnitude();
            $scaled = array_map(fn ($v) => $v * $scale, $clamped);
            return WorldStateVector::fromArray($scaled);
        }

        return $result;
    }
}
