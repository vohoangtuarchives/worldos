<?php

namespace WorldOS\Legacy\Infrastructure\EventBus;

use WorldOS\Legacy\Domain\Shared\Event\DomainEvent;

interface EventBus
{
    public function dispatch(DomainEvent $event): void;
    public function dispatchAll(array $events): void;
}
