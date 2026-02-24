<?php

namespace WorldOS\Legacy\Domain\Genre;

use WorldOS\Legacy\Domain\Genre\Contracts\GenreInterface;

abstract class AbstractGenre implements GenreInterface
{
    public function getKey(): string
    {
        return static::KEY;
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function getDescription(): string
    {
        return static::DESCRIPTION;
    }

    public function getTerminology(): array
    {
        return static::TERMINOLOGY;
    }

    public function getMaterials(): array
    {
        return static::MATERIALS;
    }

    public function getNarrativePrompt(): string
    {
        return static::PROMPT;
    }
}
