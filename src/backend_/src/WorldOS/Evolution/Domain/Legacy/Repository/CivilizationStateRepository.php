<?php

namespace WorldOS\Evolution\Domain\Legacy\Repository;

use WorldOS\Evolution\Domain\Legacy\Entity\CivilizationState;

interface CivilizationStateRepository
{
    public function save(CivilizationState $state): void;
    public function findById(string $id): ?CivilizationState;
    /** @return CivilizationState[] */
    public function findByWorld(string $worldId): array;
}

