<?php

namespace WorldOS\Infrastructure\Persistence\Evolution;

use WorldOS\Domains\Evolution\CivilizationStateRepository;
use WorldOS\Domains\Evolution\CivilizationState;

class InMemoryCivilizationStateRepository implements CivilizationStateRepository
{
    /** @var CivilizationState[] */
    private array $states = [];

    public function save(CivilizationState $state): void
    {
        $this->states[$state->getId()] = $state;
    }

    public function findById(string $id): ?CivilizationState
    {
        return $this->states[$id] ?? null;
    }

    public function findByWorld(string $worldId): array
    {
        return array_values(array_filter($this->states, fn($s) => $s->getWorldId() === $worldId));
    }
}
