<?php

namespace WorldOS\Legacy\Domain\Cosmology\Entity;

use WorldOS\Legacy\Domain\Shared\Entity\AggregateRoot;
use WorldOS\Legacy\Domain\Cosmology\Event\UniverseIgnited;
use WorldOS\Legacy\Domain\Cosmology\Entity\WorldSeed;

class Universe extends AggregateRoot
{
    private WorldSeed $seed;
    private ?string $parentUniverseId;
    private int $originTick;
    private string $timelineId;

    private function __construct(
        string $id, 
        WorldSeed $seed, 
        string $timelineId,
        ?string $parentUniverseId = null,
        int $originTick = 0
    ) {
        parent::__construct($id);
        $this->seed = $seed;
        $this->timelineId = $timelineId;
        $this->parentUniverseId = $parentUniverseId;
        $this->originTick = $originTick;
    }

    public static function ignite(string $id, WorldSeed $seed, string $timelineId): self
    {
        $universe = new self($id, $seed, $timelineId);
        $universe->record(new UniverseIgnited($id, $seed, new \DateTimeImmutable()));
        return $universe;
    }

    public static function fork(
        string $id, 
        WorldSeed $seed, 
        string $timelineId,
        string $parentUniverseId, 
        int $atTick
    ): self {
        return new self($id, $seed, $timelineId, $parentUniverseId, $atTick);
    }

    public function getSeed(): WorldSeed
    {
        return $this->seed;
    }

    public function getParentUniverseId(): ?string
    {
        return $this->parentUniverseId;
    }

    public function getOriginTick(): int
    {
        return $this->originTick;
    }

    public function getTimelineId(): string
    {
        return $this->timelineId;
    }
}
