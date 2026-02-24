<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\ValueObjects;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;

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

    /**
     * @return array<string, Attractor>
     */
    public static function catalog(): array
    {
        // Default Centroid (Middle of everything)
        $default = [
            WorldStateVector::DIMENSION_ORDER => 0.5,
            WorldStateVector::DIMENSION_ENTROPY => 0.5,
            WorldStateVector::DIMENSION_COHESION => 0.5,
            WorldStateVector::DIMENSION_LEGITIMACY => 0.5,
            WorldStateVector::DIMENSION_INNOVATION => 0.5,
            WorldStateVector::DIMENSION_MILITARY => 0.5,
            WorldStateVector::DIMENSION_INEQUALITY => 0.5,
            WorldStateVector::DIMENSION_TRAUMA => 0.5,
            WorldStateVector::DIMENSION_ELITE_COHESION => 0.5,
            WorldStateVector::DIMENSION_RESOURCE_STOCK => 0.5,
        ];

        return [
            'EQUILIBRIUM' => new self('EQUILIBRIUM', $default, $default),
            'CHAOS' => new self('CHAOS', array_merge($default, [
                WorldStateVector::DIMENSION_ORDER => 0.1,
                WorldStateVector::DIMENSION_ENTROPY => 0.9,
                WorldStateVector::DIMENSION_COHESION => 0.1,
                WorldStateVector::DIMENSION_TRAUMA => 0.8,
            ]), $default),
            'GOLDEN_AGE' => new self('GOLDEN_AGE', array_merge($default, [
                WorldStateVector::DIMENSION_ORDER => 0.8,
                WorldStateVector::DIMENSION_ENTROPY => 0.2,
                WorldStateVector::DIMENSION_INNOVATION => 0.9,
                WorldStateVector::DIMENSION_COHESION => 0.9,
            ]), $default),
            'DARK_AGE' => new self('DARK_AGE', array_merge($default, [
                WorldStateVector::DIMENSION_INNOVATION => 0.1,
                WorldStateVector::DIMENSION_ENTROPY => 0.7,
                WorldStateVector::DIMENSION_INEQUALITY => 0.9,
                WorldStateVector::DIMENSION_TRAUMA => 0.7,
            ]), $default),
        ];
    }
}
