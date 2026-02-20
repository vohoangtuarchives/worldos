<?php

declare(strict_types=1);

namespace Tuzy\Domain\Runtime\Repository;

use Tuzy\Domain\Runtime\Entity\Universe;

interface UniverseRepositoryInterface
{
    public function findById(string $id): ?Universe;

    public function save(Universe $universe): void;
}
