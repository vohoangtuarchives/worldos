<?php

namespace WorldOS\Infrastructure\Persistence\Cosmology;

use WorldOS\Domains\Cosmology\World;
use WorldOS\Domains\Cosmology\WorldRepository;

class InMemoryWorldRepository implements WorldRepository
{
    private array $worlds = [];

    public function save(World $world): void
    {
        $this->worlds[$world->getId()] = $world;
    }

    public function findById(string $id): ?World
    {
        return $this->worlds[$id] ?? null;
    }
}
