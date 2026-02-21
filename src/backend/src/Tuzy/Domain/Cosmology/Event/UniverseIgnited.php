<?php

namespace Tuzy\Domain\Cosmology\Event;

use Tuzy\Domain\Shared\Event\DomainEvent;
use Tuzy\Domain\Cosmology\Entity\WorldSeed;

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
