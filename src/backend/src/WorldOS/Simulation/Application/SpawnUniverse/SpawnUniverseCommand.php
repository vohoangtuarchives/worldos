<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Application\SpawnUniverse;

/**
 * Command to spawn a new Universe from a World Blueprint.
 */
final class SpawnUniverseCommand
{
    public function __construct(
        public readonly string $worldBlueprintId,
        public readonly string $name,
        public readonly string $multiverseId,
        public readonly int    $initialSeed,
        public readonly ?string $parentUniverseId = null,
        public readonly int    $eraIndex = 0
    ) {
    }
}
