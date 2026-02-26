<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Events;

/**
 * Bắn ra khi Rust GovernanceGuard từ chối tick do vi phạm Invariants.
 * Subscribers có thể lắng nghe để:
 *  - Ghi nhận Governance Violation vào Experiment log
 *  - Quyết định Rollback hoặc Collapse Universe
 *  - Alert thông báo cho Research UI
 */
final class TickRejected
{
    public function __construct(
        public readonly string $universeId,
        public readonly string $experimentId,
        public readonly int    $tick,
        public readonly string $regime,
        public readonly string $reason = 'Invariant violation',
    ) {}
}
