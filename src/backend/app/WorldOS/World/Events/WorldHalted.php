<?php

declare(strict_types=1);

namespace App\WorldOS\World\Events;

use App\WorldOS\World\ValueObjects\WorldId;

/**
 * Domain Event: World has been halted.
 */
final readonly class WorldHalted
{
    public function __construct(
        public WorldId $worldId,
    ) {
    }
}
