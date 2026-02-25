<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Events;

use App\Modules\Narrative\ValueObjects\SagaId;

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
