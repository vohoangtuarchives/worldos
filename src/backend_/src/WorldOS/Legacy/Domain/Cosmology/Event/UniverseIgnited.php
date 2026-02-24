<?php

namespace WorldOS\Legacy\Domain\Cosmology\Event;

use WorldOS\Legacy\Domain\Shared\Event\DomainEvent;
use WorldOS\Legacy\Domain\Cosmology\Entity\WorldSeed;

readonly class UniverseIgnited implements DomainEvent
{
    public function __construct(
        public string $universeId,
        public WorldSeed $seed,
        public \DateTimeImmutable $occurredOn
    ) {}

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
