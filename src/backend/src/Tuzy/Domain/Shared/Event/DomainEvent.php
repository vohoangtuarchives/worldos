<?php

namespace Tuzy\Domain\Shared\Event;

interface DomainEvent
{
    public function occurredOn(): \DateTimeImmutable;
}
