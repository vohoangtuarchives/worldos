<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Repository;

use WorldOS\Blueprint\Domain\Legacy\Entity\World;

interface WorldRepositoryInterface
{
    /** @return list<World> */
    public function findAll(): array;

    public function findById(string $id): ?World;

    public function save(World $world): void;

    public function delete(string $id): void;
}
