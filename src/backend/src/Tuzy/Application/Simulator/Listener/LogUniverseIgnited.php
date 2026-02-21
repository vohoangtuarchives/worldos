<?php

namespace Tuzy\Application\Simulator\Listener;

use Tuzy\Domain\Cosmology\Event\UniverseIgnited;
use Illuminate\Support\Facades\Log;

class LogUniverseIgnited
{
    public function handle(UniverseIgnited $event): void
    {
        Log::info("WorldOS V4 Event Triggered: Universe [{$event->universeId}] was ignited down the Event Bus cascade!", [
             'archetype' => $event->seed->archetype->value,
             'occurred_on' => $event->occurredOn()->format(\DateTimeInterface::ATOM)
        ]);
    }
}
