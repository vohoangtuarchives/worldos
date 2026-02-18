<?php

namespace App\Domains\Runtime\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Universe (runtime instance) was ticked. SagaContext may react (narrative, branch scoring).
 */
class UniverseTicked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $universeId,
        public ?string $worldId,
        public int $age,
        public array $stateSummary = []
    ) {
    }
}
