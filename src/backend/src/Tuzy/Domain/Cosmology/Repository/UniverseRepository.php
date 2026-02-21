<?php

namespace Tuzy\Domain\Cosmology\Repository;

use Tuzy\Domain\Cosmology\Entity\Universe;

interface UniverseRepository
{
    public function save(Universe $universe): void;
    public function findById(string $id): ?Universe;
}
