<?php

namespace Tuzy\Domain\Saga;

use Tuzy\Domain\Power\ValueObject\WorldEvent;
use Tuzy\Domain\Saga\Enums\PowerScope;
use Exception;

class SharedWorldResolver
{
    public function validateEventImpact(PowerScope $scope, WorldEvent $event): void
    {
        // A LOCAL story cannot have a GLOBAL impact event without escalation logic.
        if ($scope === PowerScope::LOCAL && $event->magnitude > 0.8) {
            throw new Exception("Local stories cannot exert Global pressure (> 0.8) on the world ledger.");
        }

        if ($scope === PowerScope::LOCAL && $event->visibility === 'public') {
            // Local public events are okay, but they shouldn't trigger global stage shifts alone.
        }
    }

    public function filterLedgerForStory(PowerScope $scope, array $ledger): array
    {
        // For a LOCAL story, only show GLOBAL events (legends) or events near its anchor.
        return array_filter($ledger, function($event) use ($scope) {
            if ($event->visibility === 'public' || $event->magnitude > 0.5) {
                return true; // Legends and Major shifts are common knowledge
            }
            return false;
        });
    }
}
