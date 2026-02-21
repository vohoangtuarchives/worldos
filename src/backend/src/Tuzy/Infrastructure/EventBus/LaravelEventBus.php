<?php

namespace Tuzy\Infrastructure\EventBus;

use Illuminate\Contracts\Events\Dispatcher;
use Tuzy\Domain\Shared\Event\DomainEvent;

class LaravelEventBus implements EventBus
{
    public function __construct(
        private Dispatcher $dispatcher
    ) {}

    public function dispatch(DomainEvent $event): void
    {
        // Broadcasts the domain event using Laravel Event Dispatcher
        $this->dispatcher->dispatch($event);
    }

    public function dispatchAll(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }
}
