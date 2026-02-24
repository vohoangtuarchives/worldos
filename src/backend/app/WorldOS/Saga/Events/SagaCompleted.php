<?php

declare(strict_types=1);

namespace App\WorldOS\Saga\Events;

use App\WorldOS\Saga\ValueObjects\SagaId;

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
