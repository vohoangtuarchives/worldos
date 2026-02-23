<?php

namespace WorldOS\Evolution\Domain\Legacy\Repository;

use WorldOS\Evolution\Domain\Legacy\Entity\WorldState;

interface WorldStateRepository
{
    public function save(WorldState $state): void;
    public function findById(string $id): ?WorldState;
}

