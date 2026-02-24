<?php

declare(strict_types=1);

namespace App\WorldOS\Saga\Events;

use App\WorldOS\Saga\ValueObjects\SagaId;

/**
 * Domain Event: A new Saga experiment has started.
 */
final readonly class SagaStarted
{
    public function __construct(
        public SagaId $sagaId,
        public string $name,
        public ?string $presetKey,
    ) {
    }
}
