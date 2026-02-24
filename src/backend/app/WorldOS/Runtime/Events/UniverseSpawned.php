<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\Events;

use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Shared\ValueObjects\Seed;
use App\WorldOS\World\ValueObjects\WorldId;

/**
 * Domain Event: A new Universe has been spawned from a World.
 */
final readonly class UniverseSpawned
{
    public function __construct(
        public UniverseId $universeId,
        public WorldId $worldId,
        public Seed $seed,
        public ?UniverseId $parentUniverseId,
    ) {
    }
}
