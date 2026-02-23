<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Application\IgniteUniverse;

/**
 * Command DTO to Ignite a new Universe from a sealed World Blueprint.
 */
final class IgniteUniverseCommand
{
    public function __construct(
        public readonly string $worldId,
        public readonly string $name,
        public readonly int    $seed = 0,
    ) {}
}
