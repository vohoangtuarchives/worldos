<?php

namespace Tuzy\Domain\Material\Repository;

use Tuzy\Domain\Faction\Entity\Faction;

interface FactionRepository
{
    public function save(Faction $faction): void;
    public function findById(string $id): ?Faction;
}
