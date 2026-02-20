<?php

namespace WorldOS\Applications\Simulator;

use WorldOS\Domains\Evolution\WorldStateRepository;
use WorldOS\Domains\Evolution\DriftOrchestrator;
use WorldOS\Infrastructure\EventBus\EventBus;
use InvalidArgumentException;

class ApplyInterventionUseCase
{
    public function __construct(
        private WorldStateRepository $repository,
        private DriftOrchestrator $orchestrator,
        private EventBus $eventBus
    ) {}

    public function execute(string $worldId, string $interventionType, float $intensity): void
    {
        $worldState = $this->repository->findById($worldId);
        
        if (!$worldState) {
            throw new InvalidArgumentException("WorldState not found for ID: " . $worldId);
        }

        $this->orchestrator->applyIntervention($worldState, $interventionType, $intensity);
        
        $this->repository->save($worldState);

        $events = $worldState->releaseEvents();
        $this->eventBus->dispatchAll($events);
    }
}
