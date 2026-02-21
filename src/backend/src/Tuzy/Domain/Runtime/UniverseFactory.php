<?php

declare(strict_types=1);

namespace Tuzy\Domain\Runtime;

use Tuzy\Application\Cosmology\Entities\Universe as CosmologyUniverse;
use Tuzy\Application\Cosmology\Services\LifecycleService;
use App\Models\World;

/**
 * Single entry point to create a Universe (runtime instance) from a World.
 * Phase 2: Saga and Admin use this so every World has at least one Universe.
 */
final class UniverseFactory
{
    public function __construct(
        private readonly LifecycleService $lifecycleService
    ) {
    }

    /**
     * Spawn one default Universe for the given World; persist and return it.
     */
    public function spawnFromWorld(World $world): CosmologyUniverse
    {
        return $this->lifecycleService->spawnNew((int) $world->id);
    }
}
