<?php

namespace App\Domains\Runtime\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Universe was forked (new branch). SagaContext may react.
 */
class UniverseForked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $sourceUniverseId,
        public string $newUniverseId,
        public ?string $worldId,
        public array $payload = []
    ) {
    }
}
