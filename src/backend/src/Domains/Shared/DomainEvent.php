<?php

namespace WorldOS\Domains\Shared;

interface DomainEvent
{
    public function occurredOn(): \DateTimeImmutable;
}
