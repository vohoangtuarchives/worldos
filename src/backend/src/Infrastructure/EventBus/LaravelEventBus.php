<?php

namespace WorldOS\Infrastructure\EventBus;

use Illuminate\Contracts\Events\Dispatcher;
use WorldOS\Domains\Shared\DomainEvent;

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
