<?php

declare(strict_types=1);

namespace App\WorldOS\Resonance\Listeners;

use App\WorldOS\Resonance\Contracts\ResonanceCheckerInterface;
use App\WorldOS\Runtime\Events\UniverseTicked;

/**
 * Check Resonance On Tick Listener.
 *
 * When the Universe completes a tick, we check if the current LawVector
 * resonates with any narrative archetypes (hero spawning).
 */
final class CheckResonanceOnTickListener
{
    public function __construct(
        private readonly ResonanceCheckerInterface $resonanceChecker,
    ) {
    }

    public function handle(UniverseTicked $event): void
    {
        // For hero spawning, we look for high resonance > 0.8
        $this->resonanceChecker->checkAll($event->stateVector, 0.8);

        // Dispatched HeroSpawnedEvent is handled separately if it occurs
    }
}
