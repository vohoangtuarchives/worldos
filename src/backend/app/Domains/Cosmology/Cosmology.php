<?php

namespace App\Domains\Cosmology;

use App\Domains\Cosmology\Aggregates\FieldSpace;
use App\Domains\Cosmology\Services\BasePhysicsEngine;

class Cosmology
{
    protected FieldSpace $fieldSpace;
    protected BasePhysicsEngine $kernel;

    public function __construct(FieldSpace $fieldSpace, BasePhysicsEngine $kernel)
    {
        $this->fieldSpace = $fieldSpace;
        $this->kernel = $kernel;
    }

    public static function boot(): self
    {
        return new self(new FieldSpace(), new BasePhysicsEngine());
    }

    public function getFieldSpace(): FieldSpace
    {
        return $this->fieldSpace;
    }

    /**
     * One step of evolution for universes in field space (BasePhysicsEngine + collapse).
     *
     * @deprecated Standalone tick. Prefer Runtime: tick Universe via UniverseRuntimeService (world_id path uses World kernel).
     *            Internal use only when Universe has no world_id (legacy).
     */
    public function tick(): void
    {
        $this->fieldSpace->evolve($this->kernel);
    }
}
