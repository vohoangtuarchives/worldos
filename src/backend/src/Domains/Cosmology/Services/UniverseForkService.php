<?php

declare(strict_types=1);

namespace WorldOS\Domains\Cosmology\Services;

use WorldOS\Domains\Cosmology\Universe;
use WorldOS\Domains\Cosmology\Timeline;
use WorldOS\Domains\Cosmology\World;
use WorldOS\Domains\Cosmology\Contracts\WorldRepositoryInterface;
use Illuminate\Support\Str;

/**
 * UniverseForkService
 * 
 * Handles the logic of branching a new Universe/Timeline from an existing one.
 */
class UniverseForkService
{
    public function __construct(
        private WorldRepositoryInterface $worldRepository
    ) {}

    /**
     * Fork a universe to create a new parallel timeline.
     */
    public function fork(Universe $source, int $atTick, string $newName): array
    {
        // 1. Create a new Timeline for the branch
        $newTimelineId = Str::uuid()->toString();
        
        // 2. Create the child Universe
        $newUniverseId = Str::uuid()->toString();
        $child = Universe::fork(
            $newUniverseId,
            $source->getSeed(),
            $newTimelineId,
            $source->getId(),
            $atTick
        );

        // 3. (Infrastructure layer will handle copying snapshots/scars later)
        // In DDD, we return the new entities and let the application service persist them.
        
        return [
            'universe' => $child,
            'timelineId' => $newTimelineId
        ];
    }
}
