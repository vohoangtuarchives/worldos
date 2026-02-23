<?php

namespace WorldOS\Legacy\Infrastructure\Persistence\Cosmology;

use WorldOS\Legacy\Domain\Cosmology\Repository\UniverseRepository;
use WorldOS\Legacy\Domain\Cosmology\Entity\Universe;

class InMemoryUniverseRepository implements UniverseRepository
{
    private array $universes = [];

    public function save(Universe $universe): void
    {
        $this->universes[$universe->getId()] = $universe;
    }

    public function findById(string $id): ?Universe
    {
        return $this->universes[$id] ?? null;
    }
}
