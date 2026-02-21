<?php

namespace Tuzy\Infrastructure\EventBus;

use Tuzy\Domain\Shared\Event\DomainEvent;

interface EventBus
{
    public function dispatch(DomainEvent $event): void;
    public function dispatchAll(array $events): void;
}
