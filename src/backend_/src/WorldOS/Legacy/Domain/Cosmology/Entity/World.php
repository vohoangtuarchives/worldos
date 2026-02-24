<?php

namespace WorldOS\Legacy\Domain\Cosmology\Entity;

use WorldOS\Legacy\Domain\Shared\Entity\AggregateRoot;
use WorldOS\Legacy\Domain\Cosmology\Enum\Archetype;
use WorldOS\Legacy\Domain\Cosmology\Entity\Universe;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\PhysicalLaws;

class World extends AggregateRoot
{
    private string $name;
    private Archetype $archetype;
    private PhysicalLaws $physicalLaws;
    private ?string $sagaId;
    /** @var Universe[] */
    private array $universes = [];

    public function __construct(string $id, string $name, Archetype $archetype, ?PhysicalLaws $physicalLaws = null, ?string $sagaId = null)
    {
        parent::__construct($id);
        $this->name = $name;
        $this->archetype = $archetype;
        $this->physicalLaws = $physicalLaws ?? PhysicalLaws::default();
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
    
    public function getPhysicalLaws(): PhysicalLaws
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
