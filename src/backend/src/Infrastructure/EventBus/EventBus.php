<?php

namespace WorldOS\Infrastructure\EventBus;

use WorldOS\Domains\Shared\DomainEvent;

interface EventBus
{
    public function dispatch(DomainEvent $event): void;
    public function dispatchAll(array $events): void;
}
