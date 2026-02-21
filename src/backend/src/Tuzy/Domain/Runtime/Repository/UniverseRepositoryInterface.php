<?php

declare(strict_types=1);

namespace Tuzy\Domain\Runtime\Repository;

use Tuzy\Domain\Runtime\Entity\Universe;

interface UniverseRepositoryInterface
{
    /** @return list<Universe> */
    public function findAll(): array;

    public function findById(string $id): ?Universe;

    public function save(Universe $universe): void;
}
