<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\Entity;

use WorldOS\Legacy\Domain\Shared\Entity\AggregateRoot;

/**
 * Timeline
 * 
 * An Aggregate Root that represents a causal chain of events and states.
 * In a Multiverse scenario, one World can have many Timelines branching from it.
 */
class Timeline extends AggregateRoot
{
    private string $worldId;
    private array $universeIds = []; // Universes that belong to this timeline

    public function __construct(string $id, string $worldId)
    {
        parent::__construct($id);
        $this->worldId = $worldId;
    }

    public function addUniverse(string $universeId): void
    {
        $this->universeIds[] = $universeId;
    }

    public function getWorldId(): string
    {
        return $this->worldId;
    }

    public function getUniverseIds(): array
    {
        return $this->universeIds;
    }
}
