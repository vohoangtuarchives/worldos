<?php

namespace WorldOS\Domains\Evolution;

interface CivilizationStateRepository
{
    public function save(CivilizationState $state): void;
    public function findById(string $id): ?CivilizationState;
    /** @return CivilizationState[] */
    public function findByWorld(string $worldId): array;
}

