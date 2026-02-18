<?php

namespace App\Domains\World\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * World laws were updated. RuntimeContext (Universe instances) may react to apply new rules.
 */
class WorldLawUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $worldId,
        public array $previousLaws,
        public array $newLaws
    ) {
    }
}
