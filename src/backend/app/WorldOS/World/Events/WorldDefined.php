<?php

declare(strict_types=1);

namespace App\WorldOS\World\Events;

use App\WorldOS\Shared\ValueObjects\LawVector;
use App\WorldOS\World\ValueObjects\WorldId;

/**
 * Domain Event: A new World has been defined.
 */
final readonly class WorldDefined
{
    public function __construct(
        public WorldId $worldId,
        public string $name,
        public LawVector $lawVector,
        public string $presetKey,
    ) {
    }
}
