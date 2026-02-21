<?php

declare(strict_types=1);

namespace Tuzy\Domain\Saga;

final class ContinuityPolicy
{
    public function enforceNoReadBackward(int $storyEpoch, array $allLedgerEvents): array
    {
        return array_filter($allLedgerEvents, fn ($event) => $event->epoch <= $storyEpoch);
    }

    public function isImmutable(int $eventEpoch, int $currentStoryEpoch): bool
    {
        return $eventEpoch < $currentStoryEpoch;
    }
}
