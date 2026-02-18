<?php

namespace App\Domains\Runtime\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Universe (runtime instance) collapsed. SagaContext may react (canonize, archive).
 */
class UniverseCollapsed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $universeId,
        public ?string $worldId,
        public string $cause,
        public array $finalState = []
    ) {
    }
}
