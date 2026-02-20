<?php

namespace WorldOS\Applications\Simulator;

use WorldOS\Domains\Cosmology\Archetype;
use WorldOS\Domains\Cosmology\Universe;
use WorldOS\Domains\Cosmology\WorldSeed;
use WorldOS\Domains\Cosmology\WorldRepository;
use WorldOS\Domains\Evolution\WorldState;
use WorldOS\Domains\Evolution\WorldStateRepository;
use WorldOS\Infrastructure\EventBus\EventBus;

class IgniteUniverseUseCase
{
    public function __construct(
        private WorldRepository $worldRepository,
        private WorldStateRepository $worldStateRepository,
        private EventBus $eventBus
    ) {}

    public function execute(string $universeId, string $archetypeStr, float $ontology, float $epistemic, float $civilization, float $energy): void
    {
        $archetype = Archetype::from($archetypeStr);
        $seed = new WorldSeed($archetype, $ontology, $epistemic, $civilization, $energy);
        
        // V4 Restoration: Mỗi Universe cần thuộc về một Timeline cụ thể
        $timelineId = \Illuminate\Support\Str::uuid()->toString();
        $universe = Universe::ignite($universeId, $seed, $timelineId);
        
        $world = $this->worldRepository->findById($universeId); // Giả định ID world trùng ID universe cho test đơn giản hoặc lấy từ context
        if (!$world) {
            $world = new \WorldOS\Domains\Cosmology\World($universeId, "World {$universeId}", $archetype);
        }
        $world->addUniverse($universe);
        
        $this->worldRepository->save($world);
        
        // Setup initial Genesis WorldState with base entropy
        $worldState = new WorldState($universeId, 0.0);
        $this->worldStateRepository->save($worldState);

        $events = $universe->releaseEvents();
        $this->eventBus->dispatchAll($events);
    }
}
