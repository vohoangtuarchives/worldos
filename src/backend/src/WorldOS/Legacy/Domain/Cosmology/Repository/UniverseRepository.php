<?php

namespace WorldOS\Legacy\Domain\Cosmology\Repository;

use WorldOS\Legacy\Domain\Cosmology\Entity\Universe;

interface UniverseRepository
{
    public function save(Universe $universe): void;
    public function findById(string $id): ?Universe;
}
