<?php

namespace WorldOS\Legacy\Domain\Material\Repository;

use WorldOS\Legacy\Domain\Material\Entity\Character;

interface CharacterRepository
{
    public function save(Character $character): void;
    public function findById(string $id): ?Character;
    
    /**
     * @return Character[]
     */
    public function findAliveByFaction(string $factionId): array;
}
