<?php

declare(strict_types=1);

namespace App\WorldOS\World\Events;

use App\WorldOS\World\ValueObjects\WorldId;

/**
 * Domain Event: World law vector has been updated.
 */
final readonly class WorldLawUpdated
{
    public function __construct(
        public WorldId $worldId,
        public string $reason,
    ) {
    }
}
