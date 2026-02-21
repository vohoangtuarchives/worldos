<?php

namespace Tuzy\Domain\Evolution\Event;

use Tuzy\Domain\Shared\Event\DomainEvent;

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

