<?php

namespace WorldOS\Applications\Simulator\Listeners;

use WorldOS\Domains\Cosmology\Events\UniverseIgnited;
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
