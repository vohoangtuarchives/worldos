<?php

namespace WorldOS\Legacy\Domain\Shared\Entity;

use WorldOS\Legacy\Domain\Shared\Event\DomainEvent;

abstract class AggregateRoot extends Entity
{
    private array $recordedEvents = [];

    protected function record(DomainEvent $event): void
    {
        $this->recordedEvents[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }
}
