<?php

declare(strict_types=1);

namespace App\Modules\Universe\Events;

use App\Modules\Universe\ValueObjects\UniverseId;
use App\Modules\Shared\ValueObjects\Seed;
use App\Modules\Universe\ValueObjects\WorldId;

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
