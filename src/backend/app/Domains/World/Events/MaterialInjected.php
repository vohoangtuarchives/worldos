<?php

namespace App\Domains\World\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Material was injected into a World. RuntimeContext may react.
 */
class MaterialInjected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $worldId,
        public string $materialId,
        public array $payload = []
    ) {
    }
}
