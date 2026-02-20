<?php

declare(strict_types=1);

namespace Tuzy\Domain\World\Repository;

use Tuzy\Domain\World\Entity\World;

interface WorldRepositoryInterface
{
    public function findById(string $id): ?World;

    public function save(World $world): void;
}
