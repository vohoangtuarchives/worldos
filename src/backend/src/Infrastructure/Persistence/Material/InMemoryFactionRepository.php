<?php

namespace WorldOS\Infrastructure\Persistence\Material;

use WorldOS\Domains\Material\FactionRepository;
use WorldOS\Domains\Material\Faction;

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
