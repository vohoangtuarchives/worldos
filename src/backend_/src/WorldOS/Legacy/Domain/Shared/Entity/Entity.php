<?php

namespace WorldOS\Legacy\Domain\Shared\Entity;

abstract class Entity
{
    protected string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function equals(Entity $other): bool
    {
        return static::class === get_class($other) && $this->id === $other->id;
    }
}
