<?php

namespace Tuzy\Domain\Evolution\Event;

use Tuzy\Domain\Shared\Event\DomainEvent;

readonly class CatastropheErupted implements DomainEvent
{
    public function __construct(
        public string $worldId,
        public string $civilizationId,
        public string $catastropheType,
        public \DateTimeImmutable $occurredOn
    ) {}

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}

