<?php

declare(strict_types=1);

namespace App\Domains\Evolution;

use App\Models\World;

/**
 * EvolutionContext - Context for one step of evolution (world, year, preset key for sensitivity).
 * Pure value object; no Eloquent reference in Vector layer.
 */
final class EvolutionContext
{
    public function __construct(
        public readonly int $worldId,
        public readonly int $year,
        public readonly string $presetKey = 'default',
        public readonly array $config = []
    ) {
    }

    public static function fromWorld(World $world, int $year): self
    {
        $config = $world->config ?? [];
        $presetKey = $config['preset_key'] ?? $world->preset ?? 'default';

        return new self(
            worldId: (int) $world->id,
            year: $year,
            presetKey: (string) $presetKey,
            config: $config
        );
    }
}
