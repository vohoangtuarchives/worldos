<?php

namespace Tuzy\Domain\Evolution\Repository;

use Tuzy\Domain\Evolution\Entity\CivilizationState;

interface CivilizationStateRepository
{
    public function save(CivilizationState $state): void;
    public function findById(string $id): ?CivilizationState;
    /** @return CivilizationState[] */
    public function findByWorld(string $worldId): array;
}

