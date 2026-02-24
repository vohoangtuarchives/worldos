<?php

declare(strict_types=1);

namespace App\WorldOS\Resonance\Listeners;

use App\WorldOS\Resonance\Contracts\ResonanceCheckerInterface;
use App\WorldOS\Resonance\Events\ResonanceDetected;
use App\WorldOS\Runtime\Events\UniverseTicked;
use App\WorldOS\Shared\ValueObjects\CascadeStateVector;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Listener: Check for heroic resonance after each Universe tick.
 *
 * Listens to UniverseTicked → runs ResonanceChecker → dispatches ResonanceDetected if triggers found.
 */
class CheckResonanceAfterTickListener
{
    public function __construct(
        private readonly ResonanceCheckerInterface $resonanceChecker,
        private readonly Dispatcher $eventDispatcher,
    ) {
    }

    public function handle(UniverseTicked $event): void
    {
        // Check for resonance triggers
        $resonanceEvents = $this->resonanceChecker->check(
            $event->stateVector,
            CascadeStateVector::initial(), // TODO: pass cascade from event in future
        );

        if (empty($resonanceEvents)) {
            return;
        }

        // Dispatch resonance detection event
        $this->eventDispatcher->dispatch(new ResonanceDetected(
            universeId: $event->universeId,
            tick: $event->tick,
            resonanceEvents: $resonanceEvents,
        ));
    }
}
