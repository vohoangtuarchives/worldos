<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Application\AdvanceTick;

use WorldOS\Chronicle\Domain\Entity\ChronicleEvent;
use WorldOS\Simulation\Domain\Engine\ValueObject\TickResult;
use WorldOS\Simulation\Domain\Engine\ValueObject\UniverseSnapshot;

/**
 * Result DTO returned by AdvanceTickHandler.
 */
final class AdvanceTickResult
{
    /**
     * @param ChronicleEvent[] $chronicleEvents
     */
    public function __construct(
        public readonly TickResult       $tickResult,
        public readonly UniverseSnapshot $snapshot,
        public readonly bool             $shouldFork,
        public readonly float            $forkPressure,
        public readonly array            $chronicleEvents = []
    ) {
    }
}

