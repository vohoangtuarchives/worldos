<?php

namespace Tuzy\Domain\Cosmology\Repository;

use Tuzy\Domain\Cosmology\Entity\World;

interface WorldRepository
{
    public function save(World $world): void;
    public function findById(string $id): ?World;
}
