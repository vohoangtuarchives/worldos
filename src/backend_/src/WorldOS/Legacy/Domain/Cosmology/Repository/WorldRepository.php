<?php

namespace WorldOS\Legacy\Domain\Cosmology\Repository;

use WorldOS\Legacy\Domain\Cosmology\Entity\World;

interface WorldRepository
{
    public function save(World $world): void;
    public function findById(string $id): ?World;
}
