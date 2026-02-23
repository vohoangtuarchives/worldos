<?php

namespace WorldOS\Legacy\Domain\Material\Repository;

use WorldOS\Society\Faction\Entity\Faction;

interface FactionRepository
{
    public function save(Faction $faction): void;
    public function findById(string $id): ?Faction;
}
