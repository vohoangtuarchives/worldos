<?php

namespace WorldOS\Legacy\Application\Simulator\UseCase;

use WorldOS\Legacy\Domain\Cosmology\Entity\Archetype;
use WorldOS\Legacy\Domain\Cosmology\Entity\Universe;
use WorldOS\Legacy\Domain\Cosmology\Entity\WorldSeed;
use WorldOS\Legacy\Domain\Cosmology\Repository\WorldRepository;
use WorldOS\Evolution\Domain\Legacy\Entity\WorldState;
use WorldOS\Evolution\Domain\Legacy\Repository\WorldStateRepository;
use WorldOS\Legacy\Infrastructure\EventBus\EventBus;

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
            $world = new \WorldOS\Legacy\Domain\Cosmology\Entity\World($universeId, "World {$universeId}", $archetype);
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
