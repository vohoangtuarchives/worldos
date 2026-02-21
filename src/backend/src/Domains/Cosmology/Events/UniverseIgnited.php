<?php

namespace WorldOS\Domains\Cosmology\Events;

use WorldOS\Domains\Shared\DomainEvent;
use WorldOS\Domains\Cosmology\WorldSeed;

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
