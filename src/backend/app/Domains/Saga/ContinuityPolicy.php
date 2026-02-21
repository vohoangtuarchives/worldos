<?php

namespace App\Domains\Saga;

use Tuzy\Domain\Saga\Enums\PowerScope;

class ContinuityPolicy
{
    /**
     * Rule: A story cannot read or modify events that happen in its temporal future.
     */
    public function enforceNoReadBackward(int $storyEpoch, array $allLedgerEvents): array
    {
        return array_filter($allLedgerEvents, function($event) use ($storyEpoch) {
            return $event->epoch <= $storyEpoch;
        });
    }

    /**
     * Rule: Shared world consequences are immutable for the past.
     */
    public function isImmutable(int $eventEpoch, int $currentStoryEpoch): bool
    {
        return $eventEpoch < $currentStoryEpoch;
    }
}
