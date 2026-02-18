<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\ValueObjects;

use App\Domains\Cosmology\Entities\WorldStateVector;

/**
 * Single attractor basin: name and centroid in WorldStateVector space.
 * origin_centroid is stored for mutation detection (drift distance).
 */
final class Attractor
{
    public function __construct(
        private readonly string $name,
        private readonly array $centroid,
        private readonly array $originCentroid
    ) {
        // centroid and originCentroid must have same keys as WorldStateVector::dimensions()
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return array<string, float> */
    public function getCentroid(): array
    {
        return $this->centroid;
    }

    /** @return array<string, float> */
    public function getOriginCentroid(): array
    {
        return $this->originCentroid;
    }

    public function distanceTo(WorldStateVector $state): float
    {
        $v = WorldStateVector::fromArray($this->centroid);
        return $state->distance($v);
    }
}
