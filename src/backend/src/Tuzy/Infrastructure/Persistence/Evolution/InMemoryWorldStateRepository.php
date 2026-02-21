<?php

namespace Tuzy\Infrastructure\Persistence\Evolution;

use Tuzy\Domain\Evolution\Entity\WorldState;
use Tuzy\Domain\Evolution\Repository\WorldStateRepository;

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
