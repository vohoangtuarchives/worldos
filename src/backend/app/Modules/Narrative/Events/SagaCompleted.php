<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Events;

use App\Modules\Narrative\ValueObjects\SagaId;

/**
 * Domain Event: A Saga experiment has completed.
 */
final readonly class SagaCompleted
{
    public function __construct(
        public SagaId $sagaId,
        public int $totalTicks,
    ) {
    }
}
