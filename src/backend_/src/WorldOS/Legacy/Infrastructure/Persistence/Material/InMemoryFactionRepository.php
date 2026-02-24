<?php

namespace WorldOS\Legacy\Infrastructure\Persistence\Material;

use WorldOS\Legacy\Domain\Material\Repository\FactionRepository;
use WorldOS\Legacy\Domain\Material\Entity\Faction;

class InMemoryFactionRepository implements FactionRepository
{
    private array $factions = [];

    public function save(Faction $faction): void
    {
        $this->factions[$faction->getId()] = $faction;
    }

    public function findById(string $id): ?Faction
    {
        return $this->factions[$id] ?? null;
    }
}
