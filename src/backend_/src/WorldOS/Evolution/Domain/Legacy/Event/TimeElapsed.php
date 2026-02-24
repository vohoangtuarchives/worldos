<?php

namespace WorldOS\Evolution\Domain\Legacy\Event;

use WorldOS\Legacy\Domain\Shared\Event\DomainEvent;

readonly class TimeElapsed implements DomainEvent
{
    public function __construct(
        public string $worldId,
        public int $yearsElapsed,
        public \DateTimeImmutable $occurredOn
    ) {}

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}

