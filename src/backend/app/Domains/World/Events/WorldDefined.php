<?php

namespace App\Domains\World\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * World (aggregate root) was created. RuntimeContext may react (e.g. create initial Universe instance).
 */
class WorldDefined
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $worldId,
        public string $name,
        public array $baselineLaws = []
    ) {
    }
}
