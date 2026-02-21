<?php

namespace Tuzy\Domain\Evolution\Event;

use Tuzy\Domain\Shared\Event\DomainEvent;

readonly class EntropyCriticalReached implements DomainEvent
{
    public function __construct(
        public string $worldId,
        public float $entropyLevel,
        public \DateTimeImmutable $occurredOn
    ) {}

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}

