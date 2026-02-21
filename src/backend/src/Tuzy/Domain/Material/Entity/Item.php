<?php

namespace Tuzy\Domain\Material\Entity;

use Tuzy\Domain\Shared\Entity\AggregateRoot;

class Item extends AggregateRoot
{
    private string $name;
    private string $type; // 'weapon', 'artifact', 'resource'

    public function __construct(string $id, string $name, string $type)
    {
        parent::__construct($id);
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
