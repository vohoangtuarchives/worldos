<?php

namespace WorldOS\Infrastructure\Persistence\Cosmology;

use WorldOS\Domains\Cosmology\UniverseRepository;
use WorldOS\Domains\Cosmology\Universe;

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
