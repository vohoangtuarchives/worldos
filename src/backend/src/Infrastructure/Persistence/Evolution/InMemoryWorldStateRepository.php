<?php

namespace WorldOS\Infrastructure\Persistence\Evolution;

use WorldOS\Domains\Evolution\WorldState;
use WorldOS\Domains\Evolution\WorldStateRepository;

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
