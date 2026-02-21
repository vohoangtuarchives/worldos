<?php

namespace Tuzy\Domain\Evolution\Repository;

use Tuzy\Domain\Evolution\Entity\WorldState;

interface WorldStateRepository
{
    public function save(WorldState $state): void;
    public function findById(string $id): ?WorldState;
}

