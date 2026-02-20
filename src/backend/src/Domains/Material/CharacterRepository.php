<?php

namespace WorldOS\Domains\Material;

interface CharacterRepository
{
    public function save(Character $character): void;
    public function findById(string $id): ?Character;
    
    /**
     * @return Character[]
     */
    public function findAliveByFaction(string $factionId): array;
}
