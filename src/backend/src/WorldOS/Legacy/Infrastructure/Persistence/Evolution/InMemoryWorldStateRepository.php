<?php

namespace WorldOS\Legacy\Infrastructure\Persistence\Evolution;

use WorldOS\Evolution\Domain\Legacy\Entity\WorldState;
use WorldOS\Evolution\Domain\Legacy\Repository\WorldStateRepository;

class InMemoryWorldStateRepository implements WorldStateRepository
{
    private array $states = [];

    public function save(WorldState $state): void
    {
        $this->states[$state->getId()] = $state;
    }

    public function findById(string $id): ?WorldState
    {
        return $this->states[$id] ?? null;
    }
}
