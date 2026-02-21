<?php

namespace WorldOS\Domains\Evolution\Events;

use WorldOS\Domains\Shared\DomainEvent;

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

