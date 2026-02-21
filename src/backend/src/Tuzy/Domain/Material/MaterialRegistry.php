<?php

namespace Tuzy\Domain\Material;

use Tuzy\Domain\Shared\Event\DomainEvent;
use Tuzy\Domain\Material\Entity\Character;
use Tuzy\Domain\Material\Entity\Faction;
use Tuzy\Domain\Material\Entity\Item;

class MaterialRegistry
{
    /** @var Character[] */
    private array $characters = [];

    /** @var Faction[] */
    private array $factions = [];

    /** @var Item[] */
    private array $items = [];

    public function addCharacter(Character $character): void
    {
        $this->characters[$character->getId()] = $character;
    }

    public function getCharacter(string $id): ?Character
    {
        return $this->characters[$id] ?? null;
    }

    public function addFaction(Faction $faction): void
    {
        $this->factions[$faction->getId()] = $faction;
    }

    public function getFaction(string $id): ?Faction
    {
        return $this->factions[$id] ?? null;
    }

    public function addItem(Item $item): void
    {
        $this->items[$item->getId()] = $item;
    }

    public function getItem(string $id): ?Item
    {
        return $this->items[$id] ?? null;
    }

    /** @return Character[] */
    public function getAllCharacters(): array
    {
        return $this->characters;
    }

    /** @return Faction[] */
    public function getAllFactions(): array
    {
        return $this->factions;
    }

    public function releaseAllEvents(): array
    {
        $events = [];

        foreach ($this->characters as $character) {
            $events = array_merge($events, $character->releaseEvents());
        }

        foreach ($this->factions as $faction) {
            $events = array_merge($events, $faction->releaseEvents());
        }

        foreach ($this->items as $item) {
            $events = array_merge($events, $item->releaseEvents());
        }

        return $events;
    }
}
