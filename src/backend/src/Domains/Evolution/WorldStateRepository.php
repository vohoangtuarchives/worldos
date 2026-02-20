<?php

namespace WorldOS\Domains\Evolution;

interface WorldStateRepository
{
    public function save(WorldState $state): void;
    public function findById(string $id): ?WorldState;
}

