<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\Projection;

/**
 * DTO for a dramatic event from simulation (tick, phase change, collapse, etc.).
 */
final class WorldEventDTO
{
    public function __construct(
        public readonly string $type,
        public readonly float $impact,
        public readonly array $stateBefore,
        public readonly array $stateAfter,
        public readonly int $tick,
        public readonly ?string $eventId = null
    ) {
    }
}
