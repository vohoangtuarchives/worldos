<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\Dto;

/**
 * DTO for spawning a new Universe from a World.
 */
final readonly class SpawnUniverseDTO
{
    /**
     * @param string $worldId         World blueprint to spawn from
     * @param string|null $name       Display name for the universe
     * @param int|null $seed          Optional specific seed (null = random)
     * @param string|null $parentUniverseId  Fork from existing universe (null = fresh spawn)
     * @param int|null $forkAtTick    If forking, which tick to fork from
     * @param array<string, mixed> $parameters  Additional universe parameters
     */
    public function __construct(
        public string $worldId,
        public ?string $name = null,
        public ?int $seed = null,
        public ?string $parentUniverseId = null,
        public ?int $forkAtTick = null,
        public array $parameters = [],
    ) {
    }
}
