<?php

namespace Tuzy\Infrastructure\Persistence\Cosmology;

use Tuzy\Domain\Cosmology\Entity\World;
use Tuzy\Domain\Cosmology\Repository\WorldRepository;

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
