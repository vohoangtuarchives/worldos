<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Events;

/**
 * Bắn ra khi 1 tick chạy thành công qua toàn bộ GovernanceGuard.
 * Subscribers có thể lắng nghe để:
 *  - Bắn sự kiện Narrative (EventExtractor)
 *  - Cập nhật trạng thái Universe
 *  - Broadcast qua WebSocket / Redis Pub/Sub
 */
final class TickCompleted
{
    public function __construct(
        public readonly string $universeId,
        public readonly string $experimentId,
        public readonly int    $tick,
        public readonly array  $nextState,
        public readonly string $nextHash,
        public readonly string $regime,
        public readonly float  $elapsedMs,
    ) {}
}
