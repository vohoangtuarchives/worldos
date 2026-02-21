<?php

namespace WorldOS\Domains\Cosmology;

use WorldOS\Domains\Shared\AggregateRoot;

class World extends AggregateRoot
{
    private string $name;
    private Archetype $archetype;
    private ValueObjects\PhysicalLaws $physicalLaws;
    private ?string $sagaId;
    /** @var Universe[] */
    private array $universes = [];

    public function __construct(string $id, string $name, Archetype $archetype, ?ValueObjects\PhysicalLaws $physicalLaws = null, ?string $sagaId = null)
    {
        parent::__construct($id);
        $this->name = $name;
        $this->archetype = $archetype;
        $this->physicalLaws = $physicalLaws ?? ValueObjects\PhysicalLaws::default();
        $this->sagaId = $sagaId;
    }

    public function getSagaId(): ?string
    {
        return $this->sagaId;
    }

    public function addUniverse(Universe $universe): void
    {
        $this->universes[] = $universe;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArchetype(): Archetype
    {
        return $this->archetype;
    }
    
    public function getPhysicalLaws(): ValueObjects\PhysicalLaws
    {
        return $this->physicalLaws;
    }

    /**
     * @return Universe[]
     */
    public function getUniverses(): array
    {
        return $this->universes;
    }
}
