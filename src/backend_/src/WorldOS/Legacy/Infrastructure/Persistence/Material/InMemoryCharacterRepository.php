<?php

namespace WorldOS\Legacy\Infrastructure\Persistence\Material;

use WorldOS\Legacy\Domain\Material\Repository\CharacterRepository;
use WorldOS\Legacy\Domain\Material\Entity\Character;

class InMemoryCharacterRepository implements CharacterRepository
{
    private array $characters = [];

    public function save(Character $character): void
    {
        $this->characters[$character->getId()] = $character;
    }

    public function findById(string $id): ?Character
    {
        return $this->characters[$id] ?? null;
    }

    public function findAliveByFaction(string $factionId): array
    {
        $result = [];
        foreach ($this->characters as $char) {
            $reflection = new \ReflectionClass($char);
            $factionProp = $reflection->getProperty('factionId'); $factionProp->setAccessible(true);
            $aliveProp = $reflection->getProperty('isAlive'); $aliveProp->setAccessible(true);
            
            if ($factionProp->getValue($char) === $factionId && $aliveProp->getValue($char)) {
                $result[] = $char;
            }
        }
        return $result;
    }
}
