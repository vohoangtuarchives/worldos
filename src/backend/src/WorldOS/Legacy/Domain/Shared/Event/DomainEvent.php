<?php

namespace WorldOS\Legacy\Domain\Shared\Event;

interface DomainEvent
{
    public function occurredOn(): \DateTimeImmutable;
}
